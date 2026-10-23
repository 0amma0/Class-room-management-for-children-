# Mockup UI — Children's Classes

Acuan visual untuk mengimplementasikan UI Laravel (Blade + Livewire 4 + Tailwind 4)
**sepersis mungkin** dengan desain Figma. Folder ini dibaca oleh developer dan AI coding assistant.

```
docs/design/
├── README.md          ← file ini
├── screens/           ← layar utuh, PNG @1x, lebar 1280 px
└── slices/            ← potongan 2 layar yang sangat tinggi (agar detailnya tidak mengecil)
```

## 1. Urutan sumber kebenaran

Jika dua sumber berbeda, yang lebih atas menang:

1. `docs/SPEC_v3.md` — aturan bisnis, hak akses, data privat, status modul.
2. **File ini** (§3 token, §4 istilah, §6 hal yang tersirat dari desain).
3. `screens/*.png` dan `slices/*.png` — tata letak dan tampilan.
4. `docs/DESIGN_SYSTEM.md` — token umum (sudah dicek cocok dengan gambar, lihat §3).

Teks di dalam gambar (bahasa Inggris) dianggap **isi resmi**, kecuali ditandai lain di §2.

## 2. Daftar layar

| File | Route / halaman | Peran | Status | Catatan |
|---|---|---|---|---|
| `01-beranda-publik.png` (+4 slice) | `/` | Publik | **Final** | Bagian: hero, Purpose, peta "Our Communities", kelas, kegiatan, statistik, footer. Angka dan kartu kelas diambil dari DB (SPEC §9: tanpa data pribadi anak) |
| `02-wilayah.png` (+2 slice) | belum ada (usul `/regions`) | Publik | **Final** | Teks "official cluster name to be confirmed" = placeholder klaster (SPEC D01) |
| `03-login.png` | `/login` | Tamu | **Final** | Tanpa "Remember me" (SPEC D29) |
| `04-dashboard-guru.png` | `/guru/dashboard` | Guru | **Final** | Cincin persen, kalender, jadwal, kartu lesson. Lihat §6 untuk data yang belum ada |
| `05-profile-settings.png` | `/profil` | Guru (+Admin) | **Final (varian Admin belum ada)** | Pemilih avatar → butuh kolom baru (§6) |
| `06-admin-akun-guru.png` | `/admin/teachers` | Admin | **Final** | Modal tambah/reset/nonaktif belum didesain |
| `07-peserta-guru.png` | belum ada (usul `/guru/students`) | Guru | **Final** | Data privat, banner peringatan wajib tampil |
| `08-perkembangan-guru.png` | `/guru/developments` | Guru | **Final** | Penilaian 4 tingkat sesuai SPEC §7.2 |
| `09-kegiatan-guru.png` | `/guru/activities` | Guru | **Final** | Alur review Admin (SPEC D08–D09); foto "pending approval" |
| `10-lesson-plan-guru-LAMA.png` | `/guru/lessons` | Guru | **JANGAN DITIRU** | Desain lama (Goal/Materials/Activities, "Create Lesson Plan", "Publish"). Bertentangan dengan SPEC §8: guru **tidak** membuat lesson; lesson = kurikulum master bagian A–G. Perlu didesain ulang |
| `11-kunjungan-ortu-guru.png` | `/guru/parent-visits` | Guru | **Final** | |
| `12-dashboard-admin.png` | `/admin/dashboard` | Admin | **Final** | Kartu "unanswered parent questions" = fitur Tahap 2 (§6) |

## 3. Token desain (diukur dari piksel gambar)

Palet dominan dari 11 layar non-beranda cocok dengan `DESIGN_SYSTEM.md`:

| Hex | Nama token | Dipakai untuk |
|---|---|---|
| `#FFF8EA` | `cream` | latar halaman (±33% area) |
| `#FFFFFF` | `white` | kartu |
| `#1F2F8A` | `navy` | sidebar, **judul/heading**, footer |
| `#8ED6EC` | `sky` | latar hero publik/login |
| `#FBA93A` | `orange` | tombol utama, menu aktif |
| `#FFD86B` | `sun` | badge peran, angka sorot |
| `#2E4BC0` | `royal` | kartu biru, tombol "Save Changes" |
| `#FFF2D4` | `accent-soft` | banner peringatan, chip |
| `#E3EAFB` | `primary-soft` | chip info, item terpilih |
| `#E1F4E8` / `#2E9E5B` | `ok-soft` / `ok` | status aktif/selesai, kartu Attendance |
| `#FCE6E6` | `danger-soft` | chip "Needs review" |
| `#65737D` | `muted` | teks sekunder |

**Belum terukur dari gambar** (harus dari Figma, lihat §7): font tiap level teks, ukuran/berat/
line-height, radius, bayangan, ketebalan border, spacing. Dugaan: Nunito (isi) dan Fredoka
(judul/angka). **Jangan menebak**; tunggu data dari §7.

### Ketidakkonsistenan yang terukur (putuskan satu nilai)

| Hal | Temuan | Usul |
|---|---|---|
| Lebar sidebar | Guru **248 px**, Admin **240 px** | Satukan di 248 px (atau 240), simpan sebagai satu variabel |
| Ikon menu | Guru (04, 07–11, 05): ikon garis (outline). Admin **12**: **emoji**. Admin **06**: **tanpa ikon** | Pakai satu keluarga ikon SVG di semua sidebar (emoji tampil beda di tiap OS) |
| Sidebar Admin | Layar 12 punya subjudul "West Java", label "NAVIGATION", dan tumpukan avatar di bawah; layar 06 tidak punya ketiganya | Tetapkan satu versi sidebar Admin |
| Istilah | "Communities" (UI) = `environments` (DB) | Lihat kamus §4 |

## 4. Kamus istilah UI ↔ domain

| Teks di desain | Tabel / konsep di kode |
|---|---|
| Regions / West Java | `regions` |
| Cluster | `clusters` |
| **Communities** | `environments` (Lingkungan: GPI, Jagabaya) |
| Classes | `classes` |
| **Participants** | `students` |
| Teachers / Teacher Accounts | `teachers` + `users` (role guru) |
| Attendance | `attendances` (Present / Excused / Sick / Absent) |
| Development | `developments` + `development_categories` |
| Activities | `activities` + `activity_photos` |
| Lesson Plan / Curriculum Lessons | `curriculum_lessons` + `class_curriculum_progress` (+ `lesson_plans` = catatan pelaksanaan) |
| Parent Visits | `parent_visits` |

## 5. Aset yang harus disediakan (target `public/images/`)

Tombol, kartu, sidebar, input, badge, dan tabel dibuat **dengan kode** — jangan diganti gambar.
Yang perlu aset:

| Aset | Format | Path usulan | Dipakai di |
|---|---|---|---|
| Logo "K" | SVG | `logo.svg` | semua layar |
| Ilustrasi hero (anak-anak bermain) | SVG | `hero/anak-bermain.svg` | 01 |
| **7 avatar**: `bangs`, `pigtails`, `afro`, `short`, `bun`, `braids`, `short-blue` | SVG | `avatars/<kunci>.svg` | 04, 05, 07, 12, tumpukan avatar |
| Ilustrasi peta komunitas (latar saja; label/kartu angka dibuat HTML) | SVG | `map/west-java.svg` | 01, 02 |
| 4 ilustrasi kegiatan (Learning Together, Creative Play, Prayers and Songs, Family Visits) | SVG | `activities/*.svg` | 01 |
| Wajah di lingkaran "Purpose" | SVG | `purpose/*.svg` | 01 |
| Pembatas awan/gelombang, bintang | SVG | `dekorasi/*.svg` | 01, 03, semua sidebar |
| Ikon menu (±15) | SVG | `icons/*.svg` | semua layar |

**Foto anak asli** (3 foto kartu kelas di 01, foto di 09) **bukan aset statis**: di aplikasi, foto
kegiatan berasal dari unggahan (`activity_photos`) dan hanya tampil publik setelah disetujui Admin
(SPEC §9.3). Untuk beranda, pakai ilustrasi atau foto berlisensi yang jelas; jangan commit foto
anak tanpa izin/lisensi.

## 6. Hal yang tersirat dari desain (jangan terlewat)

| Di desain | Konsekuensi di kode |
|---|---|
| 05: pemilih 7 avatar | Kolom baru `users.avatar` (string nullable, default `bangs`), validasi hanya 7 kunci di atas |
| 05: Display name, Username, Email, Phone (opsional), Role read-only | `users.name`, `users.username`, `users.email`, `teachers.phone`; peran **tidak bisa** diubah di sini |
| 04: "My Students" dan "+43", 12: "+7 teachers" | Perlu keputusan: avatar peserta (acak dari kunci avatar atau kolom `students.avatar`?) — **[OPEN]** |
| 04: kartu "Quote Memorization 35%" | Data hafalan = Tahap 2 (SPEC D20 **[OPEN]**). Sembunyikan kartu sampai fitur ada |
| 12: "3 unanswered parent questions" | Q&A = Tahap 2. Sembunyikan baris sampai fitur ada |
| 04: kalender + "Nearest Schedule" | Sumber data belum ditentukan (`activities`? jadwal lesson?) — **[OPEN]** |
| 09: Submit / Withdraw / "Created by Admin" / "pending approval" | Mengikuti status `activities` di SPEC §7.2 |
| 01: kartu kelas, statistik | Hanya agregat; per kelas < 3 peserta tampil "< 3" |
| **Bahasa antarmuka** | Desain berbahasa **Inggris**, SPEC D18 menulis Indonesia. **[OPEN] D36.** Sementara: salin teks desain apa adanya, bungkus dengan `__()` dan simpan di `lang/en.json` agar bahasa Indonesia bisa ditambah tanpa mengubah view |

## 7. Yang masih perlu diekspor / dibuat di Figma

Urut prioritas. **A** wajib agar bisa "persis"; **B** sangat membantu; **C** opsional.

### A. Wajib

| # | Yang diekspor | Kenapa | Cara |
|---|---|---|---|
| A1 | **Lembar komponen** (satu frame berisi semua komponen + tiap state) | Gambar layar hanya menunjukkan state default. Hover/focus/error/disabled tidak bisa ditebak | Buat frame "Components": tombol (primary/secondary/ghost/danger × default/hover/pressed/disabled/loading), input & select (default/focus/error/disabled), badge/chip semua varian, baris tabel (default/hover/dipilih), modal (tambah/edit/konfirmasi hapus/reset password), alert/toast (sukses/peringatan/error/info), paginasi, empty state. Ekspor PNG @2x |
| A2 | **Gaya teks** (font, berat, ukuran, line-height, letter-spacing) per level | Font tidak bisa diukur dari gambar | Klik teks → panel Design → Typography. Cukup screenshot panel untuk: H1, H2, H3, body, caption, tombol, label, angka besar, teks sidebar. Atau klik-kanan → **Copy/Paste as → Copy as CSS** |
| A3 | **Radius, bayangan, border, spacing** | Tidak terukur dari PNG | Screenshot panel Design untuk: kartu, tombol, input, sidebar, topbar, chip. (Dev Mode berbayar tidak wajib; "Copy as CSS" tersedia di semua paket) |
| A4 | **Versi mobile (390 px) dan tablet (768 px)** | Semua mockup sekarang hanya desktop 1280. Tanpa ini AI menebak perilaku responsif | Minimal: 01 Beranda, 03 Login, 04 Dashboard Guru, **Attendance**, 07 Participants, sidebar saat dibuka (drawer) |
| A5 | **Layar yang belum ada** | Halaman ini akan dibangun dan tidak ada acuannya | Lihat daftar di bawah |

**Layar yang belum ada (A5):**
- **Change Password** (login pertama — wajib, semua akun baru melewatinya)
- **Attendance** (desktop + mobile) — fitur paling sering dipakai guru
- **Lesson / Curriculum viewer bagian A–G** untuk guru (pengganti layar 10), dan **Curriculum manager** untuk Admin
- Admin: **Regions, Cluster, Communities, Classes** (daftar + modal Add/Edit), **Teacher Accounts** (modal 2 langkah, hasil reset password, konfirmasi nonaktifkan), **Participants**, **Activities** (review: setujui/tolak + foto)
- Publik: **detail kelas**
- **Profile Settings** versi Admin
- Halaman error **403, 404, 419, 500**; **empty state** tiap daftar; **loading/skeleton**

### B. Sangat membantu

| # | Yang diekspor | Cara |
|---|---|---|
| B1 | **Aset SVG** (tabel §5) | Pilih layer → Export → **SVG**. Satu file per avatar/ilustrasi/ikon. Cek hasil SVG membuka normal di browser |
| B2 | **Nama pustaka ikon** | Ikon sidebar terlihat seperti Lucide. Jika benar, tulis namanya (mis. `layout-dashboard`, `users`, `clipboard-check`) — tidak perlu ekspor satu-satu |
| B3 | **Nama font + tautan sumbernya** | Pastikan font dari Google Fonts (gratis) agar bisa dimuat |
| B4 | **Link prototype Figma** (klik-klik: buka modal, pilih avatar) | Untuk kamu membandingkan perilaku; AI tidak bisa membukanya |

### C. Opsional

- Ekspor ulang layar yang paling rumit (peta 01/02, cincin persen 04) @2x sebagai pembanding detail.
- Anotasi ukuran (Figma: tekan Alt lalu hover) untuk jarak yang krusial.

## 8. Cara memeriksa "sudah persis"

1. Bangun berurutan: **token → komponen (`<x-ui-*>`) → layout (sidebar/topbar) → halaman**.
2. Buka halaman di Chrome lebar **1280 px**, bandingkan dengan PNG-nya pakai ekstensi overlay
   (mis. PerfectPixel) dengan opacity 40–50%.
3. Toleransi: warna harus **identik** (hex di §3); jarak boleh meleset ≤ 2 px; font, radius,
   dan ukuran teks harus sama. Selisih lebih dari itu = bug tampilan.
4. Setelah tombol oranye terlihat dan font termuat, baru periksa fungsi (SPEC §14.1).

## 9. Prompt singkat untuk AI

```
Acuan visual: docs/design/README.md, docs/design/screens/*.png dan slices/*.png.
Warna/token ada di README §3; jika font, radius, atau spacing belum tertulis di sana, JANGAN menebak:
tulis TODO dan laporkan. Layar bertanda LAMA/belum ada jangan ditiru atau dikarang.
Teks UI disalin apa adanya dari gambar lewat __() dan lang/en.json.
Bangun urut: token -> komponen -> layout -> halaman. Setelah selesai, jalankan npm run build
dan smoke test SPEC §14.1.
```
