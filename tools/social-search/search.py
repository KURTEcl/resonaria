#!/usr/bin/env python3
"""Busca publicaciones públicas sin gastar más de una página por llamada.

Uso:
  python3 tools/social-search/search.py search --platform instagram --query "becas tic"
  python3 tools/social-search/search.py search --platform x --query "ricarte soto" --provider socialdata
  python3 tools/social-search/search.py search --platform tiktok --query "senapred" --case senapred-presupuesto-2027 --skip-loaded
  python3 tools/social-search/search.py mark-loaded --url "https://www.instagram.com/p/AbC123/"

Por defecto usa API Direct (Instagram, Reddit, X, TikTok, Threads).
SocialData solo se llama para X y solo si se pide. Si falta la clave o no hay
créditos, la salida trae "fallback": "browser".

Las búsquedas con status "ok" se guardan en tools/social-search/cache/ (o
cache/cases/<slug>/ con --case). Vuelve a correr la misma consulta sin --refresh
para reutilizar el JSON y contrastar sin gastar créditos. --skip-loaded omite
URLs ya registradas con mark-loaded.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import os
import sys
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime, timezone
from pathlib import Path

APIDIRECT_PATHS = {
    "instagram": "/v1/instagram/posts",
    "reddit": "/v1/reddit/posts",
    "tiktok": "/v1/tiktok/videos",
    "threads": "/v1/threads/posts",
    "x": "/v1/twitter/posts",
}
PLATFORMS = tuple(APIDIRECT_PATHS)
ENV_PATH = Path(__file__).with_name(".env")
DEFAULT_CACHE_DIR = Path(__file__).with_name("cache")


def load_dotenv(path: Path = ENV_PATH) -> None:
    if not path.is_file():
        return
    for raw in path.read_text(encoding="utf-8").splitlines():
        line = raw.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip().strip('"').strip("'"))


def cache_key(platform: str, query: str, provider: str) -> str:
    normalized = f"{provider.strip().lower()}:{platform.strip().lower()}:{query.strip().lower()}"
    return hashlib.sha256(normalized.encode("utf-8")).hexdigest()[:16]


def cache_search_path(cache_dir: Path, platform: str, query: str, provider: str, case: str | None) -> Path:
    name = f"{platform.strip().lower()}-{cache_key(platform, query, provider)}.json"
    if case:
        return cache_dir / "cases" / case.strip() / name
    return cache_dir / name


def loaded_urls_path(cache_dir: Path, case: str | None) -> Path:
    if case:
        return cache_dir / "cases" / case.strip() / "loaded-urls.json"
    return cache_dir / "loaded-urls.json"


def read_loaded_urls(cache_dir: Path, case: str | None) -> set[str]:
    path = loaded_urls_path(cache_dir, case)
    if not path.is_file():
        return set()
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        return set()
    if not isinstance(data, list):
        return set()
    return {str(url).strip() for url in data if str(url).strip()}


def write_loaded_urls(cache_dir: Path, case: str | None, urls: set[str]) -> Path:
    path = loaded_urls_path(cache_dir, case)
    path.parent.mkdir(parents=True, exist_ok=True)
    ordered = sorted(urls)
    path.write_text(json.dumps(ordered, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    return path


def mark_urls_loaded(cache_dir: Path, case: str | None, urls: list[str]) -> dict:
    merged = read_loaded_urls(cache_dir, case)
    added: list[str] = []
    for raw in urls:
        url = raw.strip()
        if not url:
            continue
        if url not in merged:
            added.append(url)
        merged.add(url)
    path = write_loaded_urls(cache_dir, case, merged)
    return {"status": "ok", "added": added, "total": len(merged), "file": str(path)}


def load_cached_search(path: Path) -> dict | None:
    if not path.is_file():
        return None
    try:
        envelope = json.loads(path.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        return None
    if not isinstance(envelope, dict):
        return None
    response = envelope.get("response")
    if not isinstance(response, dict):
        return None
    out = dict(response)
    out["from_cache"] = True
    out["cached_at"] = envelope.get("cached_at")
    out["cache_file"] = str(path)
    return out


def save_cached_search(
    path: Path,
    platform: str,
    query: str,
    provider: str,
    response: dict,
    raw: object,
) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    public = {k: v for k, v in response.items() if k != "raw"}
    envelope = {
        "cached_at": datetime.now(timezone.utc).isoformat(),
        "platform": platform,
        "query": query,
        "provider": provider,
        "raw": raw,
        "response": public,
    }
    path.write_text(json.dumps(envelope, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")


def filter_loaded_results(payload: dict, loaded: set[str]) -> dict:
    if not loaded or payload.get("status") != "ok":
        return payload
    results = payload.get("results")
    if not isinstance(results, list):
        return payload
    kept = [row for row in results if isinstance(row, dict) and row.get("url") not in loaded]
    skipped = len(results) - len(kept)
    out = dict(payload)
    out["results"] = kept
    out["skipped_loaded"] = skipped
    return out


def search_posts(
    platform: str,
    query: str,
    provider: str = "apidirect",
    env: dict | None = None,
    fetch=None,
    *,
    include_raw: bool = False,
) -> dict:
    platform = platform.strip().lower()
    provider = provider.strip().lower()
    query = query.strip()
    env = os.environ if env is None else env
    fetch = fetch or http_get

    base = {
        "provider": provider,
        "platform": platform,
        "query": query,
        "fallback": None,
        "results": [],
    }

    if platform not in APIDIRECT_PATHS:
        return {**base, "status": "error", "message": "Plataforma no soportada."}
    if not query:
        return {**base, "status": "error", "message": "La consulta está vacía."}
    if provider == "socialdata" and platform != "x":
        return {
            **base,
            "status": "error",
            "message": "SocialData solo cubre X. Para las demás redes usa API Direct.",
        }
    if provider not in ("apidirect", "socialdata"):
        return {**base, "status": "error", "message": "Proveedor no soportado."}

    key_name = "SOCIALDATA_API_KEY" if provider == "socialdata" else "APIDIRECT_API_KEY"
    key = str(env.get(key_name) or "").strip()
    if not key:
        return {
            **base,
            "status": "missing_key",
            "fallback": "browser",
            "message": f"Falta {key_name} en tools/social-search/.env. Busca en el navegador.",
        }

    if provider == "socialdata":
        url = "https://api.socialdata.tools/twitter/search?" + urllib.parse.urlencode(
            {"query": query, "type": "Latest"}
        )
        headers = {"Authorization": f"Bearer {key}", "Accept": "application/json"}
    else:
        url = "https://apidirect.io" + APIDIRECT_PATHS[platform] + "?" + urllib.parse.urlencode({"query": query})
        headers = {"X-API-Key": key, "Accept": "application/json"}

    try:
        status, payload = fetch(url, headers)
    except Exception as exc:  # noqa: BLE001 — la salida JSON es el contrato
        return {**base, "status": "error", "message": f"No se pudo llamar al proveedor: {exc}"}

    if status in (402, 429) or _quota_error(status, payload):
        return {
            **base,
            "status": "no_credits",
            "fallback": "browser",
            "message": f"Sin créditos en {provider}. Busca en el navegador.",
        }
    if status >= 400 or not isinstance(payload, (dict, list)):
        detail = _error_text(payload) or f"HTTP {status}"
        return {**base, "status": "error", "message": detail}

    results = [item for item in (_normalize(row, platform) for row in _rows(payload)) if item]
    out = {**base, "status": "ok", "results": results}
    if include_raw:
        out["raw"] = payload
    return out


def run_search(
    platform: str,
    query: str,
    provider: str,
    *,
    cache_dir: Path,
    case: str | None,
    use_cache: bool,
    refresh: bool,
    skip_loaded: bool,
    env: dict | None = None,
    fetch=None,
) -> dict:
    cache_path = cache_search_path(cache_dir, platform, query, provider, case)
    if use_cache and not refresh:
        cached = load_cached_search(cache_path)
        if cached is not None:
            if skip_loaded:
                loaded = read_loaded_urls(cache_dir, case)
                return filter_loaded_results(cached, loaded)
            return cached

    payload = search_posts(platform, query, provider, env=env, fetch=fetch, include_raw=True)
    if use_cache and payload.get("status") == "ok":
        raw = payload.pop("raw", None)
        save_cached_search(cache_path, platform, query, provider, payload, raw)
        payload["from_cache"] = False
        payload["cache_file"] = str(cache_path)

    if skip_loaded:
        loaded = read_loaded_urls(cache_dir, case)
        payload = filter_loaded_results(payload, loaded)

    return payload


def http_get(url: str, headers: dict) -> tuple[int, object]:
    request = urllib.request.Request(
        url,
        headers={**headers, "User-Agent": "QuePerdimosSocialSearch/1"},
    )
    try:
        with urllib.request.urlopen(request, timeout=30) as response:
            return response.status, _decode(response.read())
    except urllib.error.HTTPError as exc:
        return exc.code, _decode(exc.read())


def _decode(raw: bytes) -> object:
    text = raw.decode("utf-8", errors="replace").strip()
    if not text:
        return {}
    try:
        return json.loads(text)
    except json.JSONDecodeError:
        return text


def _rows(payload: object) -> list:
    if isinstance(payload, list):
        return payload
    if not isinstance(payload, dict):
        return []
    for key in ("posts", "videos", "tweets", "results", "data"):
        value = payload.get(key)
        if isinstance(value, list):
            return value
    return []


def _normalize(row: object, platform: str) -> dict | None:
    if not isinstance(row, dict):
        return None
    user = row.get("user") if isinstance(row.get("user"), dict) else {}
    author = _text(row.get("author") or row.get("username") or row.get("screen_name") or user.get("screen_name"))
    url = _text(row.get("url") or row.get("permalink") or row.get("link"))
    if not url and platform == "x":
        tweet_id = row.get("id_str") or row.get("id")
        if author and tweet_id:
            url = f"https://x.com/{author}/status/{tweet_id}"
    if not url:
        return None
    snippet = _text(row.get("snippet") or row.get("full_text") or row.get("text") or row.get("caption") or row.get("title"))
    return {
        "url": url,
        "author": author,
        "date": _text(row.get("date") or row.get("created_at") or row.get("published_at")),
        "snippet": snippet[:500],
        "title": _text(row.get("title"))[:300],
    }


def _text(value: object) -> str:
    if value is None:
        return ""
    return str(value).strip()


def _quota_error(status: int, payload: object) -> bool:
    if status < 400:
        return False
    text = _error_text(payload).lower()
    return any(word in text for word in ("quota", "credit", "payment required", "insufficient"))


def _error_text(payload: object) -> str:
    if isinstance(payload, str):
        return payload.strip()
    if isinstance(payload, dict):
        for key in ("message", "error", "detail"):
            value = payload.get(key)
            if isinstance(value, str) and value.strip():
                return value.strip()
    return ""


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description="Busca publicaciones públicas en redes.")
    parser.add_argument(
        "--cache-dir",
        type=Path,
        default=DEFAULT_CACHE_DIR,
        help="Carpeta para JSON de respuestas y URLs cargadas (default: tools/social-search/cache).",
    )
    parser.add_argument(
        "--case",
        default=None,
        help="Slug del tema: guarda búsquedas en cache/cases/<slug>/ y lista loaded-urls por caso.",
    )
    sub = parser.add_subparsers(dest="command", required=True)

    search = sub.add_parser("search", help="Busca en una plataforma.")
    search.add_argument("--platform", required=True, choices=PLATFORMS)
    search.add_argument("--query", required=True)
    search.add_argument("--provider", choices=("apidirect", "socialdata"), default="apidirect")
    search.add_argument("--no-cache", action="store_true", help="No leer ni escribir caché local.")
    search.add_argument("--refresh", action="store_true", help="Ignora caché y vuelve a llamar al proveedor.")
    search.add_argument(
        "--skip-loaded",
        action="store_true",
        help="Quita de results las URLs ya marcadas con mark-loaded.",
    )

    mark = sub.add_parser("mark-loaded", help="Registra URLs ya cargadas en la ficha (add_reaction).")
    mark.add_argument("--url", action="append", required=True, help="Permalink; repite --url por cada uno.")

    args = parser.parse_args(argv)
    load_dotenv()

    if args.command == "mark-loaded":
        payload = mark_urls_loaded(args.cache_dir, args.case, args.url)
        json.dump(payload, sys.stdout, ensure_ascii=False, indent=2)
        sys.stdout.write("\n")
        return 0

    payload = run_search(
        args.platform,
        args.query,
        args.provider,
        cache_dir=args.cache_dir,
        case=args.case,
        use_cache=not args.no_cache,
        refresh=args.refresh,
        skip_loaded=args.skip_loaded,
    )
    json.dump(payload, sys.stdout, ensure_ascii=False, indent=2)
    sys.stdout.write("\n")
    return 0 if payload["status"] in ("ok", "missing_key", "no_credits") else 1


if __name__ == "__main__":
    raise SystemExit(main())
