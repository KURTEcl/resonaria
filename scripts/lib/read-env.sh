#!/usr/bin/env bash
# Lee una variable KEY=VALUE de un archivo .env (sin interpretar el valor en shell).
# Uso: read_env_var ARCHIVO NOMBRE_VARIABLE
read_env_var() {
  local file=$1 key=$2 line value
  [[ -f "$file" ]] || return 1
  while IFS= read -r line || [[ -n "$line" ]]; do
    line="${line%$'\r'}"
    [[ "$line" =~ ^[[:space:]]*# ]] && continue
    [[ "$line" =~ ^[[:space:]]*$ ]] && continue
    if [[ "$line" == "${key}="* ]]; then
      value="${line#${key}=}"
      if [[ "$value" =~ ^\".*\"$ ]]; then
        value="${value:1:${#value}-2}"
      elif [[ "$value" =~ ^\'.*\'$ ]]; then
        value="${value:1:${#value}-2}"
      fi
      printf '%s' "$value"
      return 0
    fi
  done < "$file"
  return 1
}
