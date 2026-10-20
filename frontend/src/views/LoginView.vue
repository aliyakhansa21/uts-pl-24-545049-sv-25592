<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/plugins/axios' // Memanggil axios yang sudah dikonfigurasi

const email = ref('')
const password = ref('')
const router = useRouter()
const error = ref('')

const handleLogin = async () => {
    try {
        const response = await api.post('/login', {
        email: email.value,
        password: password.value
        })
        localStorage.setItem('token', response.data.token) // Menyimpan token
        router.push('/') 
    } catch (err) {
        error.value = 'Login gagal. Periksa kredensial Anda.'
    }
}
</script>

<template>
    <div>
        <h2>Login Sistem Inventaris</h2>
        <form @submit.prevent="handleLogin">
        <div><input v-model="email" type="email" placeholder="Email" required /></div>
        <div><input v-model="password" type="password" placeholder="Password" required /></div>
        <button type="submit">Login</button>
        </form>
        <p v-if="error" style="color: red;">{{ error }}</p>
    </div>
</template>