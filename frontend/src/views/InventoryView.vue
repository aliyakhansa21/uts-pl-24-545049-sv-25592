<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/plugins/axios' // Menggunakan instansiasi axios yang sudah menyimpan token

const router = useRouter()

// State untuk menyimpan daftar barang dan form
const items = ref([])
const form = ref({ name: '', quantity: 0, location: '' })
const isEditing = ref(false)
const currentId = ref(null)

// 1. TAMPILKAN DATA (Read)
const fetchItems = async () => {
  try {
    const response = await api.get('/items')
    items.value = response.data
  } catch (error) {
    console.error("Gagal mengambil data", error)
  }
}

// 2. TAMBAH & UBAH DATA (Create & Update)
const saveItem = async () => {
  try {
    if (isEditing.value) {
      // Jika mode edit, gunakan metode PUT
      await api.put(`/items/${currentId.value}`, form.value)
      isEditing.value = false
      currentId.value = null
    } else {
      // Jika tidak, gunakan metode POST untuk tambah baru
      await api.post('/items', form.value)
    }
    
    // Reset form dan ambil data terbaru
    form.value = { name: '', quantity: 0, location: '' }
    fetchItems()
  } catch (error) {
    console.error("Gagal menyimpan data", error)
  }
}

// Fungsi untuk memasukkan data ke dalam form saat tombol Edit diklik
const editItem = (item) => {
  isEditing.value = true
  currentId.value = item.id
  form.value = { name: item.name, quantity: item.quantity, location: item.location }
}

const cancelEdit = () => {
  isEditing.value = false
  currentId.value = null
  form.value = { name: '', quantity: 0, location: '' }
}

// 3. HAPUS DATA (Delete)
const deleteItem = async (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus barang ini?')) {
    try {
      await api.delete(`/items/${id}`)
      fetchItems() // Perbarui tabel setelah dihapus
    } catch (error) {
      console.error("Gagal menghapus data", error)
    }
  }
}

// Fungsi Logout untuk menghapus token
const logout = async () => {
  try {
    await api.post('/logout')
  } catch (error) {
    console.error(error)
  } finally {
    localStorage.removeItem('token') // Hapus token dari penyimpanan lokal[cite: 2]
    router.push('/login')
  }
}

// Otomatis mengambil data saat halaman dimuat
onMounted(() => {
  fetchItems()
})
</script>

<template>
  <div style="padding: 20px; font-family: sans-serif;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <h2>Sistem Manajemen Inventaris</h2>
      <button @click="logout" style="background-color: #ff4d4f; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Logout</button>
    </div>

    <!-- Form Tambah/Ubah Data -->
    <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
      <h3>{{ isEditing ? 'Edit Barang' : 'Tambah Barang Baru' }}</h3>
      <form @submit.prevent="saveItem" style="display: flex; gap: 10px;">
        <input v-model="form.name" type="text" placeholder="Nama Barang" required style="padding: 8px; flex: 1;" />
        <input v-model="form.quantity" type="number" placeholder="Jumlah" min="0" required style="padding: 8px; width: 80px;" />
        <input v-model="form.location" type="text" placeholder="Lokasi (cth: Gudang A)" required style="padding: 8px; flex: 1;" />
        <button type="submit" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
          {{ isEditing ? 'Simpan Perubahan' : 'Tambah' }}
        </button>
        <button v-if="isEditing" type="button" @click="cancelEdit" style="padding: 8px 16px; cursor: pointer;">Batal</button>
      </form>
    </div>

    <!-- Tabel Tampil Data -->
    <table border="1" style="width: 100%; border-collapse: collapse; text-align: left;">
      <thead style="background-color: #f4f4f4;">
        <tr>
          <th style="padding: 10px;">ID</th>
          <th style="padding: 10px;">Nama Barang</th>
          <th style="padding: 10px;">Jumlah</th>
          <th style="padding: 10px;">Lokasi</th>
          <th style="padding: 10px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="items.length === 0">
          <td colspan="5" style="padding: 10px; text-align: center;">Belum ada data inventaris.</td>
        </tr>
        <tr v-for="(item, index) in items" :key="item.id">
          <td style="padding: 10px;">{{ index + 1 }}</td>
          <td style="padding: 10px;">{{ item.name }}</td>
          <td style="padding: 10px;">{{ item.quantity }}</td>
          <td style="padding: 10px;">{{ item.location }}</td>
          <td style="padding: 10px;">
            <button @click="editItem(item)" style="margin-right: 5px; cursor: pointer;">Edit</button>
            <button @click="deleteItem(item.id)" style="cursor: pointer;">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>