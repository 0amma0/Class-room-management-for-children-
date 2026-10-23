# Daftar Pengembangan dan Perbaikan Sistem

## 1. Perbaikan UI dan Riwayat Notifikasi

Perbaiki tampilan UI notifikasi pada dashboard Guru dan Admin agar lebih rapi, konsisten, dan mudah digunakan. Tambahkan fitur riwayat notifikasi yang dikelompokkan atau ditampilkan berdasarkan tanggal sehingga pengguna dapat melihat notifikasi sebelumnya dengan lebih mudah.

## 2. Penggabungan Excel Data Exchange dan Database Backup

Tempatkan fitur **Excel Data Exchange** pada halaman yang sama dengan **Database Backup**.

### Dashboard Admin
Sediakan pilihan jenis data yang akan dicadangkan atau diekspor, dengan filter yang sesuai. Admin dapat memilih, misalnya:
- Data Admin.
- Data kelas anak.
- Data berdasarkan wilayah.
- Data anak berdasarkan lingkungan tertentu.

Pastikan Admin dapat menentukan cakupan data yang ingin di-backup atau diekspor melalui filter yang tersedia.

### Dashboard Guru
Sediakan fitur backup atau ekspor data yang relevan untuk Guru. Namun, backup dari dashboard Guru bukan berupa backup database SQL. Bedakan dengan jelas fitur dan jenis berkas yang tersedia untuk Guru dan Admin sesuai hak akses masing-masing.

## 3. Fitur Hapus Chat pada Inbox Bantuan

Tambahkan fitur untuk menghapus percakapan pada Inbox Bantuan di dashboard Admin dan dashboard Guru agar percakapan yang sudah tidak diperlukan dapat dibersihkan. Pastikan fitur ini mengikuti hak akses masing-masing pengguna dan memiliki perilaku yang jelas saat percakapan dihapus.

## 4. Galeri Foto Kelas dan Hasil Kegiatan Anak

Tambahkan fitur **Galeri** pada dashboard Guru yang memungkinkan Guru:
- Menyimpan dan melihat foto kegiatan kelas anak.
- Menyimpan hasil gambar atau karya yang dibuat anak-anak pada setiap sesi kegiatan.
- Melihat kembali dokumentasi tersebut melalui galeri yang tersusun rapi.

Usahakan dokumentasi dapat ditelusuri berdasarkan kelas, sesi, atau tanggal kegiatan jika data tersebut tersedia.

## 5. Fitur AI untuk Membuat Ilustrasi Cerita

Tambahkan fitur bagi Guru untuk membuat cerita dan menghasilkan ilustrasi sederhana berbasis AI dari teks cerita yang dimasukkan. Ilustrasi yang dihasilkan dapat digunakan dalam presentasi, termasuk PowerPoint (PPT).

Contoh teks cerita yang dapat digunakan untuk menguji fitur:

> Abdu’l-Bahá could always tell what was in a person’s heart, and He greatly loved people whose hearts were pure and radiant. There was a lady who had the honor of being the guest of ‘Abdu’l-Bahá at dinner. As she sat listening to His words of wisdom, she looked at a glass of water in front of her and thought, “Oh! If only ‘Abdu’l-Bahá would take my heart and empty it of every earthly desire and then refill it with Divine love and understanding, just as you would do with this glass of water.”
>
> This thought passed through her mind quickly, and she did not say anything about it, but soon something happened that made her realize ‘Abdu’l-Bahá had known what she was thinking. While He was in the middle of His talk, He paused to call over an attendant and said a few words to him softly. The attendant quietly came to the lady’s place at the table, took her glass, emptied it, and put it back in front of her. A little later, ‘Abdu’l-Bahá, while continuing to talk, picked up a pitcher of water from the table, and in a most natural way, slowly refilled the lady’s empty glass. No one noticed what had happened, but the lady knew that ‘Abdu’l-Bahá was answering her heart’s desire. She was filled with joy. Now she knew that hearts and minds were like open books to ‘Abdu’l-Bahá, Who read them with great love and kindliness.

Sediakan alur yang memungkinkan Guru memasukkan atau menyunting teks cerita, menghasilkan ilustrasi yang sesuai dengan isi cerita, lalu melihat dan menggunakan hasilnya untuk presentasi. Pertahankan teks cerita sebagai sumber utama agar ilustrasi tetap relevan dengan alur cerita.

## 6. Input Rencana Kegiatan Lain pada Lesson Plan

Tambahkan input pada fitur **Lesson Plan** untuk mencatat rencana kegiatan lain di luar kegiatan kelas anak. Pastikan Guru dapat membedakan rencana kegiatan kelas anak dan rencana kegiatan lainnya.

Tambahkan pengingat untuk Guru sebelum kelas anak dimulai, yaitu satu atau dua hari sebelumnya. Notifikasi pengingat dapat terhubung ke email Guru dan menyediakan tautan untuk menambahkan jadwal ke Google Calendar agar Guru lebih mudah mengingat jadwal kegiatan.

Gunakan gambaran UI yang mengacu pada dokumen desain **Screen 04 — Dashboard Guru** agar tampilan fitur baru tetap konsisten dengan rancangan yang sudah ada.

## 7. Pengujian Menyeluruh dan Perbaikan Error

Lakukan pengujian pada seluruh fitur yang sudah ada maupun yang baru dikembangkan. Periksa fungsi tombol, form, validasi, autentikasi, hak akses, upload file, chat, notifikasi, ekspor/backup, dan navigasi. Perbaiki bug atau error yang ditemukan, lalu lakukan pengujian ulang. Jangan menyatakan fitur berhasil jika belum diuji. Pastikan perubahan tidak merusak fitur lain yang sebelumnya berfungsi.

## 8. Membuat Akun Admin Utama

Pastikan tersedia akun Admin utama yang benar-benar dapat digunakan, bukan akun demo. Periksa mekanisme autentikasi dan proses pembuatan akun yang sudah tersedia, lalu siapkan akun melalui mekanisme yang aman. Jangan menuliskan password asli di kode sumber atau dokumentasi publik. Jika kredensial memerlukan input manual, jelaskan cara mengaturnya secara singkat.

## 9. Pilihan Kontak pada Fitur Chat

Pada fitur chat di beranda publik, berikan pilihan kepada pengunjung untuk:

- **Chat Admin:** mengirim pesan melalui sistem chat website.
- **Hubungi Sarir Imani:** `+62 813-1888-9341`.
- **Hubungi Najmi Rahmani:** `+62 896-2365-1159`.

Gunakan avatar yang sudah tersedia di project untuk desain pilihan kontak. Nomor telepon harus dapat diklik dan membuka aplikasi telepon atau WhatsApp sesuai mekanisme yang diterapkan. Jangan mengklaim WhatsApp tersedia jika tautan atau nomornya belum dapat digunakan.

Tambahkan pengaturan pada dashboard Admin agar nama dan nomor kontak person dapat diedit jika suatu saat terjadi pergantian. Perubahan harus tersimpan dan langsung diterapkan pada pilihan kontak di beranda publik tanpa perlu mengubah kode secara manual.

## 10. User Guide untuk Admin dan Guru

Tambahkan fitur **User Guide** pada masing-masing dashboard Admin dan Guru.

### User Guide Admin
Jelaskan cara mengelola akun Guru, data kelas anak, wilayah, notifikasi, chat, backup/ekspor data, dan fitur Admin lainnya yang tersedia.

### User Guide Guru
Jelaskan cara mengelola kelas, peserta, refleksi, Lesson Plan, kegiatan, galeri, chat, notifikasi, serta fitur Guru lainnya yang tersedia.

Buat panduan dalam bahasa Indonesia yang sederhana, dengan langkah-langkah yang jelas dan sesuai dengan fitur yang benar-benar tersedia. Bedakan isi panduan berdasarkan hak akses masing-masing. Gunakan desain yang konsisten dengan dashboard dan pastikan panduan mudah ditemukan serta dibaca.

## Ketentuan Tambahan

Kerjakan langsung pada project, pertahankan fungsi yang sudah berjalan, dan uji semua perubahan. Laporkan secara singkat fitur yang selesai, hasil pengujian, akun Admin yang disiapkan beserta cara mengaksesnya secara aman, dan kendala yang masih tersisa.
