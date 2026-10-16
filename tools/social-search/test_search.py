import json
import tempfile
import unittest
from pathlib import Path

import search


class SearchPostsTest(unittest.TestCase):
    def test_missing_key_does_not_call_the_network(self):
        def fetch(url, headers):
            raise AssertionError(url)

        payload = search.search_posts("instagram", "becas tic", env={}, fetch=fetch)

        self.assertEqual(payload["status"], "missing_key")
        self.assertEqual(payload["fallback"], "browser")
        self.assertEqual(payload["results"], [])

    def test_apidirect_maps_posts_and_stays_on_one_request(self):
        seen = {}

        def fetch(url, headers):
            seen["url"] = url
            seen["headers"] = headers
            return 200, {
                "posts": [
                    {
                        "title": "Becas TIC",
                        "url": "https://www.instagram.com/p/AbC123/",
                        "date": "2026-10-01",
                        "author": "ciper",
                        "snippet": "El proyecto deja la línea en cero.",
                    }
                ]
            }

        payload = search.search_posts(
            "instagram",
            "becas tic",
            env={"APIDIRECT_API_KEY": "ak_test"},
            fetch=fetch,
        )

        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["provider"], "apidirect")
        self.assertIsNone(payload["fallback"])
        self.assertIn("/v1/instagram/posts?", seen["url"])
        self.assertNotIn("pages=", seen["url"])
        self.assertEqual(seen["headers"]["X-API-Key"], "ak_test")
        self.assertEqual(payload["results"][0]["url"], "https://www.instagram.com/p/AbC123/")
        self.assertEqual(payload["results"][0]["author"], "ciper")

    def test_x_defaults_to_apidirect(self):
        def fetch(url, headers):
            self.assertIn("apidirect.io/v1/twitter/posts", url)
            self.assertNotIn("Authorization", headers)
            return 200, {"posts": []}

        payload = search.search_posts("x", "ricarte soto", env={"APIDIRECT_API_KEY": "ak_test"}, fetch=fetch)

        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["provider"], "apidirect")

    def test_payment_required_asks_for_the_browser(self):
        payload = search.search_posts(
            "tiktok",
            "infancia",
            env={"APIDIRECT_API_KEY": "ak_test"},
            fetch=lambda url, headers: (402, {"error": "Payment required"}),
        )

        self.assertEqual(payload["status"], "no_credits")
        self.assertEqual(payload["fallback"], "browser")

    def test_socialdata_is_opt_in_and_builds_status_urls(self):
        def fetch(url, headers):
            self.assertIn("api.socialdata.tools/twitter/search", url)
            self.assertIn("type=Latest", url)
            self.assertEqual(headers["Authorization"], "Bearer sd_test")
            return 200, {
                "tweets": [
                    {
                        "id_str": "99",
                        "full_text": "El fondo baja.",
                        "created_at": "Mon Oct 12 00:00:00 +0000 2026",
                        "user": {"screen_name": "cuenta"},
                    }
                ]
            }

        payload = search.search_posts(
            "x",
            "ricarte soto",
            provider="socialdata",
            env={"SOCIALDATA_API_KEY": "sd_test"},
            fetch=fetch,
        )

        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["results"][0]["url"], "https://x.com/cuenta/status/99")
        self.assertEqual(payload["results"][0]["snippet"], "El fondo baja.")

    def test_socialdata_rejects_other_platforms_without_a_request(self):
        def fetch(url, headers):
            raise AssertionError(url)

        payload = search.search_posts(
            "reddit",
            "presupuesto",
            provider="socialdata",
            env={"SOCIALDATA_API_KEY": "sd_test"},
            fetch=fetch,
        )

        self.assertEqual(payload["status"], "error")
        self.assertIsNone(payload["fallback"])

    def test_cache_reuses_json_without_second_request(self):
        with tempfile.TemporaryDirectory() as tmp:
            cache_dir = Path(tmp)

            def fetch(url, headers):
                return 200, {
                    "posts": [
                        {
                            "url": "https://www.instagram.com/p/AbC123/",
                            "author": "ciper",
                            "snippet": "Uno.",
                        }
                    ]
                }

            first = search.run_search(
                "instagram",
                "becas tic",
                "apidirect",
                cache_dir=cache_dir,
                case=None,
                use_cache=True,
                refresh=False,
                skip_loaded=False,
                env={"APIDIRECT_API_KEY": "ak_test"},
                fetch=fetch,
            )
            self.assertEqual(first["status"], "ok")
            self.assertFalse(first["from_cache"])
            self.assertEqual(len(first["results"]), 1)

            def fail_fetch(url, headers):
                raise AssertionError("no debería llamar de nuevo")

            second = search.run_search(
                "instagram",
                "becas tic",
                "apidirect",
                cache_dir=cache_dir,
                case=None,
                use_cache=True,
                refresh=False,
                skip_loaded=False,
                env={"APIDIRECT_API_KEY": "ak_test"},
                fetch=fail_fetch,
            )
            self.assertTrue(second["from_cache"])
            self.assertEqual(second["results"][0]["url"], "https://www.instagram.com/p/AbC123/")

            cache_file = search.cache_search_path(cache_dir, "instagram", "becas tic", "apidirect", None)
            envelope = json.loads(cache_file.read_text(encoding="utf-8"))
            self.assertIn("raw", envelope)
            self.assertIn("response", envelope)

    def test_skip_loaded_filters_results(self):
        with tempfile.TemporaryDirectory() as tmp:
            cache_dir = Path(tmp)
            search.mark_urls_loaded(cache_dir, "demo", ["https://www.instagram.com/p/AbC123/"])

            payload = search.run_search(
                "instagram",
                "becas",
                "apidirect",
                cache_dir=cache_dir,
                case="demo",
                use_cache=False,
                refresh=True,
                skip_loaded=True,
                env={"APIDIRECT_API_KEY": "ak_test"},
                fetch=lambda url, headers: (
                    200,
                    {
                        "posts": [
                            {"url": "https://www.instagram.com/p/AbC123/", "snippet": "viejo"},
                            {"url": "https://www.instagram.com/p/XyZ999/", "snippet": "nuevo"},
                        ]
                    },
                ),
            )

            self.assertEqual(payload["status"], "ok")
            self.assertEqual(payload["skipped_loaded"], 1)
            self.assertEqual(len(payload["results"]), 1)
            self.assertEqual(payload["results"][0]["url"], "https://www.instagram.com/p/XyZ999/")


if __name__ == "__main__":
    unittest.main()
