<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/plugins/axios'
import {
  Package, Plus, Pencil, Trash2, LogOut, Save, X, Search,
  Sparkles, Star, MapPin, Hash, AlertCircle, CheckCircle2,
  LayoutDashboard, Boxes, WarehouseIcon, AlertTriangle
} from '@lucide/vue'

const router = useRouter()

// State
const items = ref([])
const form = ref({ name: '', quantity: 0, location: '' })
const isEditing = ref(false)
const currentId = ref(null)
const searchQuery = ref('')
const showDeleteModal = ref(false)
const deleteTargetId = ref(null)
const deleteTargetName = ref('')
const isLoading = ref(false)

// Toast
const toast = ref({ show: false, message: '', type: 'success' })

const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

// Validation
const touched = ref({ name: false, quantity: false, location: false })

const nameError = computed(() => {
  if (!touched.value.name) return ''
  if (!form.value.name.trim()) return 'Nama barang wajib diisi'
  if (form.value.name.trim().length < 2) return 'Nama minimal 2 karakter'
  return ''
})

const quantityError = computed(() => {
  if (!touched.value.quantity) return ''
  if (form.value.quantity === '' || form.value.quantity === null) return 'Jumlah wajib diisi'
  if (Number(form.value.quantity) < 0) return 'Jumlah tidak boleh negatif'
  return ''
})

const locationError = computed(() => {
  if (!touched.value.location) return ''
  if (!form.value.location.trim()) return 'Lokasi wajib diisi'
  return ''
})

const isFormValid = computed(() => {
  return form.value.name.trim() &&
    form.value.quantity !== '' &&
    Number(form.value.quantity) >= 0 &&
    form.value.location.trim() &&
    !nameError.value &&
    !quantityError.value &&
    !locationError.value
})

// Filtered items
const filteredItems = computed(() => {
  if (!searchQuery.value.trim()) return items.value
  const q = searchQuery.value.toLowerCase()
  return items.value.filter(item =>
    item.name.toLowerCase().includes(q) ||
    item.location.toLowerCase().includes(q)
  )
})

// Dashboard stats
const totalItems = computed(() => items.value.length)
const totalQuantity = computed(() => items.value.reduce((sum, i) => sum + Number(i.quantity), 0))
const uniqueLocations = computed(() => new Set(items.value.map(i => i.location)).size)
const lowStockItems = computed(() => items.value.filter(i => Number(i.quantity) <= 5).length)

// 1. READ
const fetchItems = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/items')
    items.value = response.data
  } catch (error) {
    showToast('Gagal mengambil data', 'error')
    console.error("Gagal mengambil data", error)
  } finally {
    isLoading.value = false
  }
}

// 2. CREATE & UPDATE
const saveItem = async () => {
  touched.value = { name: true, quantity: true, location: true }
  if (!isFormValid.value) return

  try {
    if (isEditing.value) {
      await api.put(`/items/${currentId.value}`, form.value)
      showToast('Barang berhasil diperbarui ✦')
      isEditing.value = false
      currentId.value = null
    } else {
      await api.post('/items', form.value)
      showToast('Barang berhasil ditambahkan ✦')
    }
    form.value = { name: '', quantity: 0, location: '' }
    touched.value = { name: false, quantity: false, location: false }
    fetchItems()
  } catch (error) {
    showToast('Gagal menyimpan data', 'error')
    console.error("Gagal menyimpan data", error)
  }
}

// Edit
const editItem = (item) => {
  isEditing.value = true
  currentId.value = item.id
  form.value = { name: item.name, quantity: item.quantity, location: item.location }
  touched.value = { name: false, quantity: false, location: false }
  // Scroll to form on mobile
  document.getElementById('inventory-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

const cancelEdit = () => {
  isEditing.value = false
  currentId.value = null
  form.value = { name: '', quantity: 0, location: '' }
  touched.value = { name: false, quantity: false, location: false }
}

// 3. DELETE
const confirmDelete = (item) => {
  deleteTargetId.value = item.id
  deleteTargetName.value = item.name
  showDeleteModal.value = true
}

const deleteItem = async () => {
  try {
    await api.delete(`/items/${deleteTargetId.value}`)
    showToast('Barang berhasil dihapus ✦')
    fetchItems()
  } catch (error) {
    showToast('Gagal menghapus data', 'error')
    console.error("Gagal menghapus data", error)
  } finally {
    showDeleteModal.value = false
    deleteTargetId.value = null
    deleteTargetName.value = ''
  }
}

// Logout
const logout = async () => {
  try {
    await api.post('/logout')
  } catch (error) {
    console.error(error)
  } finally {
    localStorage.removeItem('token')
    router.push('/login')
  }
}

onMounted(() => {
  fetchItems()
})
</script>

<template>
  <div class="min-h-screen relative z-10">
    <!-- ===== TOAST ===== -->
    <Transition name="toast">
      <div
        v-if="toast.show"
        class="toast-y2k"
        :class="toast.type === 'success' ? 'toast-success' : 'toast-error'"
      >
        <div class="flex items-center gap-2">
          <CheckCircle2 v-if="toast.type === 'success'" class="w-4 h-4" />
          <AlertCircle v-else class="w-4 h-4" />
          <span>{{ toast.message }}</span>
        </div>
      </div>
    </Transition>

    <!-- ===== HEADER / NAVBAR ===== -->
    <header class="sticky top-0 z-40 border-b border-[rgba(255,105,180,0.12)]"
            style="background: rgba(13, 0, 26, 0.85); backdrop-filter: blur(16px);">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Brand -->
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#ff69b4] to-[#ff00ff] flex items-center justify-center shadow-[0_0_15px_rgba(255,0,255,0.3)]">
              <Package class="w-4 h-4 text-white" />
            </div>
            <div>
              <h1 class="font-pixel text-[0.5rem] sm:text-[0.6rem] holo-text tracking-wider leading-none">
                INVENTARIS
              </h1>
              <p class="text-[0.55rem] text-[#c8a2ff]/40 font-body mt-0.5 hidden sm:block">
                Management System
              </p>
            </div>
          </div>

          <!-- Right side -->
          <div class="flex items-center gap-3">
            <span class="stat-badge stat-badge-cyan hidden sm:inline-flex">
              <Sparkles class="w-3 h-3" />
              Y2K Mode
            </span>
            <button @click="logout" class="btn-y2k btn-y2k-danger text-[0.65rem] py-2 px-4">
              <LogOut class="w-3.5 h-3.5" />
              <span class="hidden sm:inline">Logout</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      <!-- ===== DASHBOARD STATS ===== -->
      <section>
        <div class="flex items-center gap-2 mb-4">
          <LayoutDashboard class="w-4 h-4 text-[#00e5ff]" />
          <h2 class="font-pixel text-[0.5rem] text-[#00e5ff] text-glow-cyan tracking-wider">
            DASHBOARD
          </h2>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          <!-- Stat 1: Total Items -->
          <div class="glass-card p-4 sm:p-5 group hover:border-[rgba(255,105,180,0.4)] transition-colors">
            <div class="flex items-start justify-between mb-2">
              <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#ff69b4]/20 to-[#ff00ff]/10 flex items-center justify-center border border-[rgba(255,105,180,0.15)]">
                <Boxes class="w-4 h-4 text-[#ff69b4]" />
              </div>
              <span class="stat-badge stat-badge-pink">
                <Star class="w-2.5 h-2.5" />
                Total
              </span>
            </div>
            <p class="font-pixel text-lg sm:text-xl text-white text-glow-pink">{{ totalItems }}</p>
            <p class="text-[0.7rem] text-[#c8a2ff]/40 mt-1">Jenis Barang</p>
          </div>

          <!-- Stat 2: Total Quantity -->
          <div class="glass-card p-4 sm:p-5 group hover:border-[rgba(0,229,255,0.4)] transition-colors">
            <div class="flex items-start justify-between mb-2">
              <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#00e5ff]/20 to-[#00b8d4]/10 flex items-center justify-center border border-[rgba(0,229,255,0.15)]">
                <Hash class="w-4 h-4 text-[#00e5ff]" />
              </div>
              <span class="stat-badge stat-badge-cyan">
                <Star class="w-2.5 h-2.5" />
                Qty
              </span>
            </div>
            <p class="font-pixel text-lg sm:text-xl text-white text-glow-cyan">{{ totalQuantity }}</p>
            <p class="text-[0.7rem] text-[#c8a2ff]/40 mt-1">Total Unit</p>
          </div>

          <!-- Stat 3: Locations -->
          <div class="glass-card p-4 sm:p-5 group hover:border-[rgba(200,162,255,0.4)] transition-colors">
            <div class="flex items-start justify-between mb-2">
              <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#c8a2ff]/20 to-[#9b59b6]/10 flex items-center justify-center border border-[rgba(200,162,255,0.15)]">
                <MapPin class="w-4 h-4 text-[#c8a2ff]" />
              </div>
              <span class="stat-badge stat-badge-lavender">
                <Star class="w-2.5 h-2.5" />
                Loc
              </span>
            </div>
            <p class="font-pixel text-lg sm:text-xl text-white" style="text-shadow: 0 0 10px rgba(200,162,255,0.4)">{{ uniqueLocations }}</p>
            <p class="text-[0.7rem] text-[#c8a2ff]/40 mt-1">Lokasi Gudang</p>
          </div>

          <!-- Stat 4: Low Stock -->
          <div class="glass-card p-4 sm:p-5 group hover:border-[rgba(255,64,96,0.4)] transition-colors">
            <div class="flex items-start justify-between mb-2">
              <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#ff4060]/20 to-[#ff0040]/10 flex items-center justify-center border border-[rgba(255,64,96,0.15)]">
                <AlertTriangle class="w-4 h-4 text-[#ff6b8a]" />
              </div>
              <span class="stat-badge" style="background:rgba(255,64,96,0.12);color:#ff6b8a;border:1px solid rgba(255,64,96,0.2)">
                <Star class="w-2.5 h-2.5" />
                Alert
              </span>
            </div>
            <p class="font-pixel text-lg sm:text-xl text-white" style="text-shadow: 0 0 10px rgba(255,64,96,0.4)">{{ lowStockItems }}</p>
            <p class="text-[0.7rem] text-[#c8a2ff]/40 mt-1">Stok Rendah (≤5)</p>
          </div>
        </div>
      </section>

      <hr class="neon-divider" />

      <!-- ===== FORM SECTION ===== -->
      <section id="inventory-form">
        <div class="flex items-center gap-2 mb-4">
          <component :is="isEditing ? Pencil : Plus" class="w-4 h-4 text-[#ff69b4]" />
          <h2 class="font-pixel text-[0.5rem] text-[#ff69b4] text-glow-pink tracking-wider">
            {{ isEditing ? '✦ EDIT BARANG ✦' : '✦ TAMBAH BARANG ✦' }}
          </h2>
        </div>

        <div class="glass-card p-5 sm:p-6" :class="{ 'border-[rgba(0,229,255,0.35)]': isEditing }">
          <form @submit.prevent="saveItem">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
              <!-- Name -->
              <div>
                <label class="block text-[0.6rem] font-pixel text-[#ff69b4] mb-2 tracking-wider">
                  NAMA BARANG
                </label>
                <div class="relative">
                  <Package class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#c8a2ff]/30" />
                  <input
                    v-model="form.name"
                    @blur="touched.name = true"
                    type="text"
                    placeholder="cth: Laptop ASUS"
                    class="input-y2k pl-10"
                    :class="{
                      'input-error': nameError,
                      'input-success': touched.name && !nameError && form.name
                    }"
                    required
                  />
                </div>
                <p v-if="nameError" class="validation-msg error">
                  <AlertCircle class="w-3 h-3" /> {{ nameError }}
                </p>
                <p v-else-if="touched.name && !nameError && form.name" class="validation-msg success">
                  <CheckCircle2 class="w-3 h-3" /> Valid
                </p>
              </div>

              <!-- Quantity -->
              <div>
                <label class="block text-[0.6rem] font-pixel text-[#ff69b4] mb-2 tracking-wider">
                  JUMLAH
                </label>
                <div class="relative">
                  <Hash class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#c8a2ff]/30" />
                  <input
                    v-model="form.quantity"
                    @blur="touched.quantity = true"
                    type="number"
                    placeholder="0"
                    min="0"
                    class="input-y2k pl-10"
                    :class="{
                      'input-error': quantityError,
                      'input-success': touched.quantity && !quantityError
                    }"
                    required
                  />
                </div>
                <p v-if="quantityError" class="validation-msg error">
                  <AlertCircle class="w-3 h-3" /> {{ quantityError }}
                </p>
                <p v-else-if="touched.quantity && !quantityError" class="validation-msg success">
                  <CheckCircle2 class="w-3 h-3" /> Valid
                </p>
              </div>

              <!-- Location -->
              <div>
                <label class="block text-[0.6rem] font-pixel text-[#ff69b4] mb-2 tracking-wider">
                  LOKASI
                </label>
                <div class="relative">
                  <MapPin class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#c8a2ff]/30" />
                  <input
                    v-model="form.location"
                    @blur="touched.location = true"
                    type="text"
                    placeholder="cth: Gudang A"
                    class="input-y2k pl-10"
                    :class="{
                      'input-error': locationError,
                      'input-success': touched.location && !locationError && form.location
                    }"
                    required
                  />
                </div>
                <p v-if="locationError" class="validation-msg error">
                  <AlertCircle class="w-3 h-3" /> {{ locationError }}
                </p>
                <p v-else-if="touched.location && !locationError && form.location" class="validation-msg success">
                  <CheckCircle2 class="w-3 h-3" /> Valid
                </p>
              </div>
            </div>

            <!-- Form Buttons -->
            <div class="flex flex-wrap gap-3 mt-5 pt-4 border-t border-[rgba(255,105,180,0.1)]">
              <button type="submit" class="btn-y2k" :class="isEditing ? 'btn-y2k-cyan' : 'btn-y2k-pink'">
                <component :is="isEditing ? Save : Plus" class="w-4 h-4" />
                {{ isEditing ? 'Simpan Perubahan' : 'Tambah Barang' }}
              </button>
              <button
                v-if="isEditing"
                type="button"
                @click="cancelEdit"
                class="btn-y2k btn-y2k-chrome"
              >
                <X class="w-4 h-4" />
                Batal
              </button>
            </div>
          </form>
        </div>
      </section>

      <hr class="neon-divider" />

      <!-- ===== TABLE SECTION ===== -->
      <section>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
          <div class="flex items-center gap-2">
            <Package class="w-4 h-4 text-[#00e5ff]" />
            <h2 class="font-pixel text-[0.5rem] text-[#00e5ff] text-glow-cyan tracking-wider">
              ✦ DAFTAR INVENTARIS ✦
            </h2>
          </div>

          <!-- Search -->
          <div class="relative w-full sm:w-64">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#c8a2ff]/30" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari barang atau lokasi..."
              class="input-y2k pl-10 text-sm py-2"
            />
          </div>
        </div>

        <div class="glass-card-cyan overflow-hidden">
          <!-- Loading -->
          <div v-if="isLoading" class="p-12 text-center">
            <div class="inline-flex flex-col items-center gap-3">
              <svg class="animate-spin h-8 w-8 text-[#ff69b4]" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <span class="font-pixel text-[0.5rem] text-[#c8a2ff]/50 tracking-wider">LOADING...</span>
            </div>
          </div>

          <!-- Empty state -->
          <div v-else-if="filteredItems.length === 0 && !searchQuery" class="p-12 text-center">
            <div class="inline-flex flex-col items-center gap-3">
              <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#ff69b4]/10 to-[#ff00ff]/5 flex items-center justify-center border border-[rgba(255,105,180,0.15)]">
                <Boxes class="w-7 h-7 text-[#ff69b4]/40" />
              </div>
              <p class="font-pixel text-[0.5rem] text-[#c8a2ff]/40 tracking-wider">BELUM ADA DATA</p>
              <p class="text-sm text-[#c8a2ff]/30">Tambah barang pertama Anda di atas ✦</p>
            </div>
          </div>

          <!-- No search results -->
          <div v-else-if="filteredItems.length === 0 && searchQuery" class="p-12 text-center">
            <div class="inline-flex flex-col items-center gap-3">
              <Search class="w-8 h-8 text-[#c8a2ff]/20" />
              <p class="font-pixel text-[0.5rem] text-[#c8a2ff]/40 tracking-wider">TIDAK DITEMUKAN</p>
              <p class="text-sm text-[#c8a2ff]/30">Tidak ada hasil untuk "{{ searchQuery }}"</p>
            </div>
          </div>

          <!-- Table -->
          <div v-else class="table-container">
            <table class="table-y2k">
              <thead>
                <tr>
                  <th class="w-16">#</th>
                  <th>Nama Barang</th>
                  <th class="w-24">Jumlah</th>
                  <th>Lokasi</th>
                  <th class="w-36 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in filteredItems" :key="item.id">
                  <td>
                    <span class="font-pixel text-[0.55rem] text-[#c8a2ff]/50">
                      {{ String(index + 1).padStart(2, '0') }}
                    </span>
                  </td>
                  <td>
                    <div class="flex items-center gap-2">
                      <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-[#ff69b4]/15 to-[#ff00ff]/5 flex items-center justify-center border border-[rgba(255,105,180,0.12)] shrink-0">
                        <Package class="w-3.5 h-3.5 text-[#ff69b4]/60" />
                      </div>
                      <span class="font-medium text-white/90">{{ item.name }}</span>
                    </div>
                  </td>
                  <td>
                    <span
                      class="stat-badge"
                      :class="Number(item.quantity) <= 5 ? 'bg-[rgba(255,64,96,0.12)] text-[#ff6b8a] border border-[rgba(255,64,96,0.2)]' : 'stat-badge-cyan'"
                    >
                      {{ item.quantity }}
                    </span>
                  </td>
                  <td>
                    <div class="flex items-center gap-1.5 text-[#c8a2ff]/70">
                      <MapPin class="w-3 h-3 shrink-0" />
                      <span>{{ item.location }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="flex items-center justify-center gap-2">
                      <button
                        @click="editItem(item)"
                        class="btn-y2k btn-y2k-cyan text-[0.6rem] py-1.5 px-3"
                        title="Edit"
                      >
                        <Pencil class="w-3 h-3" />
                        <span class="hidden md:inline">Edit</span>
                      </button>
                      <button
                        @click="confirmDelete(item)"
                        class="btn-y2k btn-y2k-danger text-[0.6rem] py-1.5 px-3"
                        title="Hapus"
                      >
                        <Trash2 class="w-3 h-3" />
                        <span class="hidden md:inline">Hapus</span>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Table Footer -->
          <div v-if="filteredItems.length > 0" class="px-4 py-3 border-t border-[rgba(0,229,255,0.1)] flex items-center justify-between">
            <p class="text-[0.7rem] text-[#c8a2ff]/30">
              Menampilkan <span class="text-[#00e5ff]">{{ filteredItems.length }}</span> dari <span class="text-[#00e5ff]">{{ items.length }}</span> barang
            </p>
            <Sparkles class="w-3 h-3 text-[#ff69b4]/30" />
          </div>
        </div>
      </section>

      <!-- ===== FOOTER ===== -->
      <footer class="text-center py-6">
        <hr class="neon-divider mb-4" />
        <p class="font-pixel text-[0.45rem] text-[#c8a2ff]/25 tracking-widest">
          ✦ Y2K INVENTORY MANAGEMENT SYSTEM ✦
        </p>
      </footer>
    </main>

    <!-- ===== DELETE CONFIRMATION MODAL ===== -->
    <Transition name="modal">
      <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
        <div class="glass-card p-6 sm:p-8 max-w-sm w-full mx-4 text-center">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#ff4060]/15 to-[#ff0040]/5 flex items-center justify-center border border-[rgba(255,64,96,0.2)] mx-auto mb-4">
            <Trash2 class="w-6 h-6 text-[#ff6b8a]" />
          </div>
          <h3 class="font-pixel text-[0.55rem] text-[#ff6b8a] tracking-wider mb-2">
            KONFIRMASI HAPUS
          </h3>
          <p class="text-sm text-[#e0d0ff]/70 mb-1">
            Apakah Anda yakin ingin menghapus
          </p>
          <p class="text-white font-semibold mb-6">
            "{{ deleteTargetName }}"?
          </p>
          <div class="flex gap-3 justify-center">
            <button @click="showDeleteModal = false" class="btn-y2k btn-y2k-chrome">
              <X class="w-4 h-4" />
              Batal
            </button>
            <button @click="deleteItem" class="btn-y2k btn-y2k-danger">
              <Trash2 class="w-4 h-4" />
              Hapus
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
/* Toast animation */
.toast-enter-active {
  animation: toast-in 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(-16px);
}

/* Modal animation */
.modal-enter-active {
  transition: all 0.3s ease;
}
.modal-leave-active {
  transition: all 0.2s ease;
}
.modal-enter-from {
  opacity: 0;
}
.modal-enter-from .glass-card {
  transform: scale(0.9);
}
.modal-leave-to {
  opacity: 0;
}
</style>
