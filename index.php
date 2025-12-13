<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Keuangan Kos</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: radial-gradient(circle at 50% 0%, #1e293b 0%, #0f172a 40%, #020617 100%);
            color: #e2e8f0; 
        }
        
        .glass-panel {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .glass-input {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        .glass-input:focus {
            outline: none;
            border-color: #06b6d4;
            background: rgba(15, 23, 42, 0.8);
        }

      
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        .fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
        .fade-enter-from, .fade-leave-to { opacity: 0; }
    </style>
</head>
<body class="min-h-screen relative">

    <div id="app">
        
        <div class="h-48 w-full bg-cover bg-center object-cover relative group transition-all duration-500 ease-in-out" 
             :style="{ backgroundImage: 'url(' + currentCover + ')' }">
             <div class="absolute inset-0 bg-gradient-to-b from-transparent to-[#0f172a]"></div>
             
             <div class="absolute bottom-4 right-6 opacity-0 group-hover:opacity-100 transition">
                 <button @click="changeCover" class="bg-slate-900/60 text-xs text-white px-3 py-1.5 rounded-full hover:bg-slate-800 backdrop-blur-md border border-white/10 shadow-lg active:scale-95 transition flex items-center gap-2">
                    Ganti Cover
                 </button>
             </div>
        </div>

        <div class="max-w-5xl mx-auto px-6 relative pb-20 -mt-20">
            
            <div class="flex justify-between items-end mb-8 pl-2">
                <div class="flex flex-col relative z-10">
                    <div class="text-7xl drop-shadow-2xl hover:scale-105 transition transform cursor-pointer mb-2">💎</div>
                    <h1 class="text-4xl font-bold text-white tracking-tight">
                        Halo, <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500"><?= $_SESSION['username'] ?></span>!
                    </h1>
                </div>

                <div class="flex gap-2 mb-2 relative z-10">
                    <button @click="exportToCSV" class="glass-panel text-xs text-slate-300 hover:text-white hover:bg-slate-700/50 px-4 py-2 rounded-lg transition flex items-center gap-2">
                        📂 Export CSV
                    </button>
                    <a href="logout.php" class="glass-panel text-xs text-rose-400 hover:text-rose-300 hover:bg-rose-900/20 px-4 py-2 rounded-lg transition border-rose-500/20">
                        🚪 Logout
                    </a>
                </div>
            </div>

            <div class="mb-8 group">
                <div class="glass-panel p-4 rounded-xl border-l-4 border-l-yellow-500/80 hover:bg-slate-800/40 transition shadow-lg">
                    <h3 class="text-yellow-500/80 font-bold text-[10px] uppercase tracking-widest mb-2">Quick Notes</h3>
                    <textarea v-model="notes" @input="saveNotes" class="w-full bg-transparent border-none focus:ring-0 resize-none text-slate-300 text-sm leading-relaxed placeholder-slate-600" rows="2" placeholder="Catatan penting..."></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="glass-panel p-5 rounded-2xl hover:bg-slate-800/40 transition relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition"></div>
                    <p class="text-xs text-slate-400 uppercase font-semibold tracking-wider">Sisa Saldo</p>
                    <h2 class="text-3xl font-bold text-white mt-1">Rp {{ formatRupiah(summary.balance) }}</h2>
                </div>
                <div class="glass-panel p-5 rounded-2xl hover:bg-slate-800/40 transition relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-cyan-500/10 rounded-full blur-xl group-hover:bg-cyan-500/20 transition"></div>
                    <p class="text-xs text-slate-400 uppercase font-semibold tracking-wider">Pemasukan</p>
                    <h2 class="text-2xl font-bold text-cyan-400 mt-1">+ {{ formatRupiah(summary.income) }}</h2>
                </div>
                <div class="glass-panel p-5 rounded-2xl hover:bg-slate-800/40 transition relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-rose-500/10 rounded-full blur-xl group-hover:bg-rose-500/20 transition"></div>
                    <p class="text-xs text-slate-400 uppercase font-semibold tracking-wider">Pengeluaran</p>
                    <h2 class="text-2xl font-bold text-rose-400 mt-1">- {{ formatRupiah(summary.expense) }}</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                
                <div class="lg:col-span-2" id="formInputArea">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-white/5">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            {{ isEditing ? '✏️ Edit Transaksi' : '➕ Transaksi Baru' }}
                        </h3>
                        <span v-if="isEditing" class="text-[10px] bg-yellow-500/10 text-yellow-400 px-2 py-1 rounded border border-yellow-500/20">
                            Mode Edit
                        </span>
                    </div>

                    <form @submit.prevent="handleSubmit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs text-slate-400 ml-1">Nama Transaksi</label>
                                <input v-model="form.description" type="text" placeholder="Keterangan" class="glass-input w-full p-3 rounded-xl text-sm transition" required>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs text-slate-400 ml-1">Nominal (Rp)</label>
                                <input v-model="form.amount" type="number" placeholder="0" class="glass-input w-full p-3 rounded-xl text-sm transition" required>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs text-slate-400 ml-1">Tipe</label>
                                <select v-model="form.type" class="glass-input w-full p-3 rounded-xl text-sm transition">
                                    <option value="out" class="bg-slate-900">Pengeluaran</option>
                                    <option value="in" class="bg-slate-900">Pemasukan</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs text-slate-400 ml-1">Tanggal</label>
                                <input v-model="form.date" type="date" class="glass-input w-full p-3 rounded-xl text-sm transition" required>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button v-if="isEditing" type="button" @click="cancelEdit" class="w-1/3 py-3 rounded-xl text-sm font-medium text-slate-400 border border-white/10 hover:bg-white/5 transition">
                                Batal
                            </button>
                            
                            <button type="submit" 
                                class="w-full text-white py-3 rounded-xl text-sm font-bold shadow-lg transition transform active:scale-95"
                                :class="isEditing ? 'bg-gradient-to-r from-yellow-600 to-orange-600 hover:shadow-orange-500/20' : 'bg-gradient-to-r from-cyan-600 to-blue-600 hover:shadow-cyan-500/20'">
                                {{ isEditing ? 'Update Perubahan' : 'Simpan Transaksi' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="glass-panel rounded-2xl p-6 flex flex-col items-center justify-center relative">
                    <h3 class="text-xs text-slate-500 mb-6 absolute top-4 left-6 uppercase font-bold tracking-widest">Analisis</h3>
                    <div class="w-40 h-40 mb-6 relative mt-4">
                        <canvas id="myChart"></canvas>
                    </div>
                    <div class="grid grid-cols-2 gap-3 w-full">
                        <div class="bg-slate-900/50 p-3 rounded-xl border border-white/5 text-center">
                            <p class="text-[10px] text-slate-500 uppercase">Masuk</p>
                            <p class="text-md font-bold text-cyan-400">{{ percentages.income }}%</p>
                        </div>
                        <div class="bg-slate-900/50 p-3 rounded-xl border border-white/5 text-center">
                            <p class="text-[10px] text-slate-500 uppercase">Keluar</p>
                            <p class="text-md font-bold text-rose-400">{{ percentages.expense }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b border-white/5 flex items-center gap-2 text-white">
                    <span>🗂️</span> Riwayat Transaksi
                </h3>
                <div class="overflow-x-auto glass-panel rounded-xl">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-900/50 text-slate-400 font-medium border-b border-white/5">
                            <tr>
                                <th class="p-4 w-32">Tanggal</th>
                                <th class="p-4">Keterangan</th>
                                <th class="p-4">Tipe</th>
                                <th class="p-4 text-right">Jumlah</th>
                                <th class="p-4 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-300">
                            <tr v-for="t in transactions" :key="t.id" class="hover:bg-white/5 transition group">
                                <td class="p-4 text-slate-500 text-xs">{{ t.date }}</td>
                                <td class="p-4 font-medium text-white">{{ t.description }}</td>
                                <td class="p-4">
                                    <span :class="t.type == 'in' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'" class="px-2.5 py-1 rounded-md text-xs font-medium">
                                        {{ t.type == 'in' ? 'Pemasukan' : 'Pengeluaran' }}
                                    </span>
                                </td>
                                <td class="p-4 text-right font-mono">{{ formatRupiah(t.amount) }}</td>
                                <td class="p-4 text-center flex justify-center gap-3">
                                    <button @click="editRow(t)" class="text-slate-500 hover:text-yellow-400 transition" title="Edit">✏️</button>
                                    <button @click="openDeleteModal(t.id)" class="text-slate-500 hover:text-rose-500 transition" title="Hapus">🗑️</button>
                                </td>
                            </tr>
                            <tr v-if="transactions.length === 0">
                                <td colspan="5" class="p-10 text-center text-slate-600 italic">Belum ada data transaksi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <transition name="fade">
        <div v-if="showSuccessModal" class="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="glass-panel border border-white/10 rounded-2xl w-full max-w-sm p-8 shadow-2xl transform flex flex-col items-center text-center">
                <div class="h-16 w-16 bg-cyan-500/10 rounded-full flex items-center justify-center border border-cyan-500/30 text-3xl mb-4 shadow-[0_0_20px_rgba(6,182,212,0.2)]">
                    ✅
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Berhasil!</h3>
                <p class="text-sm text-slate-400 mb-6">{{ successMessage }}</p>
                <button @click="showSuccessModal = false" class="bg-slate-800 hover:bg-slate-700 text-white w-full py-3 rounded-xl border border-white/10 transition font-medium">
                    Tutup
                </button>
            </div>
        </div>
        </transition>

        <transition name="fade">
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="glass-panel border border-white/10 rounded-2xl w-full max-w-sm p-6 shadow-2xl transform">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-rose-500/10 mb-5 border border-rose-500/20 text-2xl shadow-[0_0_15px_rgba(244,63,94,0.2)]">
                    🗑️
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-bold text-white mb-2">Hapus Transaksi?</h3>
                    <p class="text-sm text-slate-400 mb-6 leading-relaxed">
                        Data yang dihapus tidak dapat dikembalikan.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false" 
                        class="w-1/2 py-2.5 rounded-xl text-sm font-medium text-slate-300 border border-white/10 hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button @click="confirmDelete" 
                        class="w-1/2 py-2.5 rounded-xl text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 shadow-lg transition">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
        </transition>

    </div>

    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    transactions: [],
                    summary: { income: 0, expense: 0, balance: 0, user: '' },
                    form: { description: '', amount: '', type: 'out', date: new Date().toISOString().split('T')[0] },
                    
                    isEditing: false,
                    editingId: null,
                    showDeleteModal: false,
                    deleteId: null,
                    showSuccessModal: false,
                    successMessage: '',
                    
                    chartInstance: null,
                    notes: '',
                    currentUser: '',
                    
                
                    currentCover: 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?q=80&w=1500&auto=format&fit=crop',
                    coverOptions: [
                        'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?q=80&w=1500&auto=format&fit=crop',
                        'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?q=80&w=1500&auto=format&fit=crop', 
                        'https://images.unsplash.com/photo-1493246507139-91e8fad9978e?q=80&w=1500&auto=format&fit=crop',   
                        'https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=1500&auto=format&fit=crop',
                        'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=1500&auto=format&fit=crop',
                        'https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=1500&auto=format&fit=crop', 
                        'https://images.unsplash.com/photo-1534224039826-c7a0eda0e6b3?q=80&w=1500&auto=format&fit=crop'  
                    ]
                }
            },
            computed: {
                percentages() {
                    const total = parseFloat(this.summary.income) + parseFloat(this.summary.expense);
                    if (total === 0) return { income: 0, expense: 0 };
                    const incomePct = (this.summary.income / total) * 100;
                    const expensePct = (this.summary.expense / total) * 100;
                    return { income: incomePct.toFixed(1), expense: expensePct.toFixed(1) };
                }
            },
            mounted() {
                this.fetchData();
             
            },
            methods: {
                showSuccess(message) {
                    this.successMessage = message;
                    this.showSuccessModal = true;
                    setTimeout(() => { this.showSuccessModal = false; }, 2000);
                },

          
                changeCover() {
                    let currentIndex = this.coverOptions.indexOf(this.currentCover);
                    let nextIndex = currentIndex + 1;
                    if(nextIndex >= this.coverOptions.length) nextIndex = 0;
                    
                    this.currentCover = this.coverOptions[nextIndex];
                    
                    if (this.currentUser) {
                        localStorage.setItem('cover_' + this.currentUser, this.currentCover);
                    }
                },
            

                editRow(item) {
                    this.isEditing = true;
                    this.editingId = item.id;
                    this.form = JSON.parse(JSON.stringify(item));
                    document.getElementById('formInputArea').scrollIntoView({ behavior: 'smooth' });
                },
                cancelEdit() {
                    this.isEditing = false;
                    this.editingId = null;
                    this.resetForm();
                },
                handleSubmit() {
                    if (this.isEditing) {
                        this.updateTransaction();
                    } else {
                        this.saveTransaction();
                    }
                },
                updateTransaction() {
                    const payload = { ...this.form, id: this.editingId };
                    axios.put('api.php', payload)
                        .then(response => {
                            if(response.data.status === 'success') {
                                this.showSuccess("Data berhasil diupdate!");
                                this.cancelEdit();
                                this.fetchData();
                            } else {
                                alert("Gagal update: " + response.data.message);
                            }
                        })
                        .catch(error => console.error(error));
                },
                openDeleteModal(id) {
                    this.deleteId = id;
                    this.showDeleteModal = true;
                },
                confirmDelete() {
                    if(this.deleteId) {
                        axios.delete(`api.php?id=${this.deleteId}`).then(res => {
                            this.showDeleteModal = false;
                            this.deleteId = null;
                            this.fetchData();
                        });
                    }
                },
                resetForm() {
                    this.form = { description: '', amount: '', type: 'out', date: new Date().toISOString().split('T')[0] };
                },
                exportToCSV() {
                    if (this.transactions.length === 0) return alert("Belum ada data!");
                    let csvContent = "data:text/csv;charset=utf-8,Tanggal,Keterangan,Tipe,Jumlah\n";
                    this.transactions.forEach(row => {
                        let type = row.type === 'in' ? 'Pemasukan' : 'Pengeluaran';
                        csvContent += `${row.date},"${row.description}",${type},${row.amount}\n`;
                    });
                    const encodedUri = encodeURI(csvContent);
                    const link = document.createElement("a");
                    link.setAttribute("href", encodedUri);
                    link.setAttribute("download", "Laporan_Kos.csv");
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                saveNotes() {
                    if (this.currentUser) {
                        const storageKey = 'notes_' + this.currentUser;
                        localStorage.setItem(storageKey, this.notes);
                    }
                },
                formatRupiah(angka) {
                    return new Intl.NumberFormat('id-ID').format(angka);
                },
                fetchData() {
                    axios.get('api.php')
                        .then(response => {
                            this.transactions = response.data.transactions;
                            this.summary = response.data.summary;
                            
                        
                            this.currentUser = response.data.summary.user; 

                        
                            const notesKey = 'notes_' + this.currentUser;
                            if (localStorage.getItem(notesKey)) {
                                this.notes = localStorage.getItem(notesKey);
                            } else {
                                this.notes = '';
                            }

                         
                            const coverKey = 'cover_' + this.currentUser;
                            if (localStorage.getItem(coverKey)) {
                                this.currentCover = localStorage.getItem(coverKey);
                            } else {
                              
                                this.currentCover = this.coverOptions[0];
                            }

                            this.renderChart();
                        })
                        .catch(error => console.error("Error fetching data:", error));
                },
                saveTransaction() {
                    axios.post('api.php', this.form)
                        .then(response => {
                            if(response.data.status === 'success') {
                                this.showSuccess("Transaksi berhasil disimpan!");
                                this.resetForm();
                                this.fetchData(); 
                            } else {
                                alert('Gagal menyimpan');
                            }
                        });
                },
                renderChart() {
                    const ctx = document.getElementById('myChart');
                    if(this.chartInstance) this.chartInstance.destroy();
                    this.chartInstance = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Pemasukan', 'Pengeluaran'],
                            datasets: [{
                                data: [this.summary.income, this.summary.expense],
                                backgroundColor: ['#22d3ee', '#f43f5e'], 
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            cutout: '75%'
                        }
                    });
                }
            }
        }).mount('#app');
    </script>
</body>
</html>