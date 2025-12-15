# Panduan Deploy ke GitHub

Langkah-langkah lengkap untuk mendeploy project Interactive English Lab ke GitHub.

## Persiapan

### 1. Pastikan Git sudah terinstall
```bash
git --version
```
Jika belum terinstall, download dari: https://git-scm.com/downloads

### 2. Buat akun GitHub (jika belum punya)
- Kunjungi: https://github.com
- Daftar akun baru atau login

## Langkah-langkah Deploy

### Step 1: Buat Repository di GitHub

1. Login ke GitHub
2. Klik tombol **"+"** di kanan atas → pilih **"New repository"**
3. Isi form:
   - **Repository name**: `englishlab` (atau nama lain yang diinginkan)
   - **Description**: "Interactive English Learning Platform"
   - **Visibility**: Pilih **Public** (gratis) atau **Private** (perlu GitHub Pro)
   - **JANGAN** centang "Initialize with README" (karena kita sudah punya file)
4. Klik **"Create repository"**

### Step 2: Inisialisasi Git di Project Lokal

Buka terminal/command prompt di folder project (`C:\xampp\htdocs\englishlab`):

```bash
# Inisialisasi git repository
git init

# Tambahkan semua file ke staging
git add .

# Commit pertama
git commit -m "Initial commit: Interactive English Lab project"
```

### Step 3: Hubungkan dengan GitHub Repository

```bash
# Tambahkan remote repository (ganti YOUR_USERNAME dengan username GitHub kamu)
git remote add origin https://github.com/YOUR_USERNAME/englishlab.git

# Atau jika menggunakan SSH:
# git remote add origin git@github.com:YOUR_USERNAME/englishlab.git
```

### Step 4: Push ke GitHub

```bash
# Push ke branch main/master
git branch -M main
git push -u origin main
```

Jika diminta login:
- **Username**: username GitHub kamu
- **Password**: Gunakan **Personal Access Token** (bukan password biasa)
  - Cara buat token: GitHub → Settings → Developer settings → Personal access tokens → Generate new token
  - Beri permission: `repo` (full control)

### Step 5: Verifikasi

1. Refresh halaman repository di GitHub
2. Pastikan semua file sudah ter-upload
3. Pastikan file `config/db.php` **TIDAK** ada di repository (karena ada di .gitignore)

## File yang TIDAK akan di-upload (sudah di .gitignore)

- ✅ `config/db.php` - File konfigurasi database (berisi password)
- ✅ `assets/img/uploads/*` - File upload user
- ✅ `assets/uploads/*` - File upload course
- ✅ File log dan temporary

## Setelah Deploy

### Untuk Developer Lain yang Clone Project

1. Clone repository:
   ```bash
   git clone https://github.com/YOUR_USERNAME/englishlab.git
   cd englishlab
   ```

2. Buat file `config/db.php` berdasarkan `config/db.php.example`:
   ```bash
   cp config/db.php.example config/db.php
   # Edit config/db.php dengan kredensial database lokal
   ```

3. Import database:
   - Buka phpMyAdmin
   - Import file `config/database.sql`

4. Buat folder uploads:
   ```bash
   mkdir -p assets/img/uploads
   mkdir -p assets/uploads/courses
   ```

## Update Project ke GitHub

Setelah melakukan perubahan, update ke GitHub:

```bash
# Lihat status perubahan
git status

# Tambahkan file yang berubah
git add .

# Commit dengan pesan deskriptif
git commit -m "Deskripsi perubahan yang dilakukan"

# Push ke GitHub
git push origin main
```

## Tips

1. **Jangan commit file sensitif**: Pastikan `config/db.php` tidak pernah di-commit
2. **Commit message yang jelas**: Gunakan pesan yang menjelaskan apa yang diubah
3. **Pull sebelum push**: Jika bekerja dalam tim, selalu pull dulu sebelum push
   ```bash
   git pull origin main
   git push origin main
   ```

## Troubleshooting

### Error: "remote origin already exists"
```bash
git remote remove origin
git remote add origin https://github.com/YOUR_USERNAME/englishlab.git
```

### Error: "failed to push some refs"
```bash
git pull origin main --rebase
git push origin main
```

### Lupa password/token GitHub
- Buat Personal Access Token baru di: https://github.com/settings/tokens
- Gunakan token sebagai password saat push

## Selesai! 🎉

Project kamu sekarang sudah di GitHub dan bisa diakses oleh siapa saja (jika public) atau hanya orang yang kamu beri akses (jika private).

