<script setup lang="ts">
const props = defineProps<{
  title: string
  section: 'temas' | 'perfiles'
  phase: 'closed' | 'login' | 'desk'
  userName?: string
  loginTitle?: string
  loginLead?: string
  error?: string
  pending?: boolean
}>()

defineEmits<{
  enter: []
  leave: []
}>()

const route = useRoute()

const email = defineModel<string>('email', { default: '' })
const password = defineModel<string>('password', { default: '' })
</script>

<template>
  <main class="desk-page">
    <div class="desk-page__inner">
      <p v-if="phase === 'closed'" class="desk-page__missing">Esta página no existe.</p>

      <form v-else-if="phase === 'login'" class="desk-login" @submit.prevent="$emit('enter')">
        <h1>{{ loginTitle || 'Mesa editorial' }}</h1>
        <p v-if="loginLead" class="desk-login__lead">{{ loginLead }}</p>
        <label class="desk-field">
          Correo
          <input v-model="email" type="email" autocomplete="username" required>
        </label>
        <label class="desk-field">
          Clave
          <input v-model="password" type="password" autocomplete="current-password" required>
        </label>
        <p v-if="error" class="desk-alert" role="alert">{{ error }}</p>
        <button class="btn btn--primary" type="submit" :disabled="pending">Entrar</button>
      </form>

      <template v-else>
        <header class="desk-shell__top">
          <div class="desk-shell__brand">
            <h1>{{ title }}</h1>
            <span class="desk-shell__badge">Cuaderno</span>
          </div>
          <nav class="desk-shell__nav" aria-label="Secciones">
            <NuxtLink
              :class="{ 'is-active': section === 'temas' }"
              active-class="is-active"
              :to="{ path: '/cuaderno', query: route.query }"
            >
              Temas
            </NuxtLink>
            <NuxtLink
              :class="{ 'is-active': section === 'perfiles' }"
              active-class="is-active"
              :to="{ path: '/cuaderno/perfiles', query: route.query }"
            >
              Perfiles
            </NuxtLink>
          </nav>
          <span v-if="userName" class="desk-shell__user">{{ userName }}</span>
          <button class="btn btn--quiet" type="button" @click="$emit('leave')">Salir</button>
        </header>

        <p v-if="error" class="desk-alert" role="alert">{{ error }}</p>

        <slot />
      </template>
    </div>
  </main>
</template>
