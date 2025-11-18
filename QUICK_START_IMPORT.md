# Quick Start - Real-Time Import Progress

## Langkah-Langkah Penggunaan

### 1️⃣ Jalankan Queue Worker (WAJIB)
Buka terminal baru dan jalankan:
```bash
run_queue.bat
```
**PENTING:** Jangan tutup window terminal ini! Biarkan tetap berjalan.

### 2️⃣ Jalankan Server Laravel
Di terminal lain, jalankan:
```bash
run_server.bat
```
atau
```bash
php artisan serve
```

### 3️⃣ Akses Aplikasi
Buka browser: `http://localhost:8000`

### 4️⃣ Login sebagai Admin
Gunakan kredensial admin

### 5️⃣ Import Data
1. Klik menu **"Data Karyawan"**
2. Scroll ke bagian **"Upload Data Massal"**
3. Pilih file Excel (.xlsx atau .csv)
4. Klik tombol **"Tambah"** atau **"Ganti Semua"**
5. Halaman progress akan muncul dengan animasi loading

### 6️⃣ Lihat Progress Real-Time
Progress bar akan menampilkan:
- ✅ Persentase (0-100%)
- ✅ Status: "Memproses data: X baris..."
- ✅ Animasi loading

Progress akan update setiap 0.5 detik secara otomatis!

### 7️⃣ Selesai
Setelah 100%, halaman akan otomatis redirect ke daftar data karyawan dalam 3 detik.

## Troubleshooting Cepat

**Progress tidak update?**
→ Pastikan `run_queue.bat` masih berjalan

**Import error?**
→ Cek `storage/logs/laravel.log`

**Want to stop?**
→ Tekan `Ctrl+C` di terminal queue worker

## File yang Perlu Dijalankan
```
Terminal 1: run_queue.bat    ← Queue Worker (WAJIB)
Terminal 2: run_server.bat   ← Laravel Server
```

## Demo Flow
```
Upload File → Progress Page → 
    ↓
[Progress Bar: 0%] Memulai import...
    ↓
[Progress Bar: 20%] Membaca file Excel...
    ↓
[Progress Bar: 50%] Memproses data: 2500 baris...
    ↓
[Progress Bar: 75%] Memproses data: 5000 baris...
    ↓
[Progress Bar: 100%] Import Berhasil! ✓
    ↓
Auto redirect ke Data Karyawan
```

Selamat mencoba! 🚀
