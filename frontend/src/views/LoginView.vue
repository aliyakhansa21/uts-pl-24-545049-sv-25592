<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/plugins/axios'
import { LogIn, Mail, Lock, AlertCircle, Sparkles, Star } from '@lucide/vue'

const email = ref('')
const password = ref('')
const router = useRouter()
const error = ref('')
const isLoading = ref(false)

// Validation state
const touched = ref({ email: false, password: false })

const emailError = computed(() => {
    if (!touched.value.email) return ''
    if (!email.value) return 'Email wajib diisi'
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) return 'Format email tidak valid'
    return ''
})

const passwordError = computed(() => {
    if (!touched.value.password) return ''
    if (!password.value) return 'Password wajib diisi'
    if (password.value.length < 6) return 'Password minimal 6 karakter'
    return ''
})

const isFormValid = computed(() => {
    return email.value && password.value && !emailError.value && !passwordError.value
})

const handleLogin = async () => {
    touched.value = { email: true, password: true }
    if (!isFormValid.value) return

    isLoading.value = true
    error.value = ''
    try {
        const response = await api.post('/login', {
        email: email.value,
        password: password.value
        })
        localStorage.setItem('token', response.data.token)
        router.push('/')
    } catch (err) {
        error.value = 'Login gagal. Periksa kredensial Anda.'
    } finally {
        isLoading.value = false
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center p-4 relative z-10">
        <!-- Decorative floating elements -->
        <div class="fixed top-[10%] left-[8%] opacity-20 pointer-events-none">
        <Star class="w-6 h-6 text-[#ff69b4] animate-pulse" />
        </div>
        <div class="fixed top-[25%] right-[12%] opacity-15 pointer-events-none">
        <Sparkles class="w-8 h-8 text-[#00e5ff] animate-pulse" style="animation-delay: 1s" />
        </div>
        <div class="fixed bottom-[15%] left-[15%] opacity-15 pointer-events-none">
        <Star class="w-5 h-5 text-[#c8a2ff] animate-pulse" style="animation-delay: 0.5s" />
        </div>
        <div class="fixed bottom-[30%] right-[8%] opacity-20 pointer-events-none">
        <Sparkles class="w-6 h-6 text-[#ff69b4] animate-pulse" style="animation-delay: 1.5s" />
        </div>

        <div class="w-full max-w-md">
        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 mb-3">
            <Sparkles class="w-5 h-5 text-[#ff69b4]" />
            <span class="font-pixel text-[0.5rem] tracking-widest text-[#c8a2ff] uppercase">System</span>
            <Sparkles class="w-5 h-5 text-[#00e5ff]" />
            </div>
            <h1 class="font-pixel text-lg md:text-xl holo-text leading-relaxed">
            INVENTARIS
            </h1>
            <p class="text-[#c8a2ff]/60 text-sm mt-2 font-body">
            Masuk untuk mengelola inventaris Anda
            </p>
        </div>

        <!-- Login Card -->
        <div class="glass-card p-8">
            <h2 class="font-pixel text-[0.6rem] text-[#00e5ff] text-glow-cyan mb-6 text-center tracking-wider">
            ✦ LOGIN ✦
            </h2>

            <!-- Error Alert -->
            <Transition name="fade">
            <div
                v-if="error"
                class="mb-5 p-3 rounded-xl bg-[rgba(255,64,96,0.1)] border border-[rgba(255,64,96,0.25)] flex items-center gap-2 text-[#ff6b8a] text-sm"
            >
                <AlertCircle class="w-4 h-4 shrink-0" />
                <span>{{ error }}</span>
            </div>
            </Transition>

            <form @submit.prevent="handleLogin" class="space-y-5">
            <!-- Email Field -->
            <div>
                <label class="block text-[0.7rem] font-pixel text-[#ff69b4] mb-2 tracking-wider">
                EMAIL
                </label>
                <div class="relative">
                <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#c8a2ff]/40" />
                <input
                    v-model="email"
                    @blur="touched.email = true"
                    type="email"
                    placeholder="you@example.com"
                    class="input-y2k pl-10"
                    :class="{
                    'input-error': emailError,
                    'input-success': touched.email && !emailError && email
                    }"
                    required
                />
                </div>
                <p v-if="emailError" class="validation-msg error">
                <AlertCircle class="w-3 h-3" />
                {{ emailError }}
                </p>
            </div>

            <!-- Password Field -->
            <div>
                <label class="block text-[0.7rem] font-pixel text-[#ff69b4] mb-2 tracking-wider">
                PASSWORD
                </label>
                <div class="relative">
                <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#c8a2ff]/40" />
                <input
                    v-model="password"
                    @blur="touched.password = true"
                    type="password"
                    placeholder="••••••••"
                    class="input-y2k pl-10"
                    :class="{
                    'input-error': passwordError,
                    'input-success': touched.password && !passwordError && password
                    }"
                    required
                />
                </div>
                <p v-if="passwordError" class="validation-msg error">
                <AlertCircle class="w-3 h-3" />
                {{ passwordError }}
                </p>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="isLoading"
                class="btn-y2k btn-y2k-pink w-full justify-center text-sm py-3 mt-2"
                :class="{ 'opacity-60 cursor-not-allowed': isLoading }"
            >
                <LogIn class="w-4 h-4" />
                <span v-if="!isLoading">Masuk</span>
                <span v-else class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Memproses...
                </span>
            </button>
            </form>

            <!-- Footer -->
            <div class="mt-6 text-center">
            <hr class="neon-divider mb-4" />
            <p class="text-[0.6rem] text-[#c8a2ff]/30 font-pixel tracking-wider">
                ✦ Y2K INVENTORY SYS ✦
            </p>
            </div>
        </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
