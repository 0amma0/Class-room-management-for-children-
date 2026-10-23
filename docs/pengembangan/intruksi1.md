# Instruksi Pengembangan — Sistem Pengelolaan Kelas Anak

## Aturan Utama
- Implementasikan semua poin berikut pada project yang sudah ada.
- Pertahankan struktur, alur perubahan, fitur, dan desain yang sudah berjalan. Jangan mengubah bagian di luar kebutuhan tanpa alasan jelas.
- Jadikan mockup di `docs/design/screens` sebagai referensi UI utama.
- Pastikan fitur benar-benar berfungsi, terhubung ke database, memiliki validasi, dan mengikuti hak akses Admin/Guru.
- Periksa implementasi yang ada sebelum mengubah kode; perbaiki fitur yang error, bukan sekadar mengganti tampilannya.

## 1. Filter Participants
Pada halaman **Participants**, tetap sediakan fitur tambah participant. Tambahkan filter berdasarkan:
- Region/Wilayah
- Klaster
- Lingkungan

Filter harus dapat digunakan untuk mempermudah pencarian dan pengelolaan data peserta, termasuk ketika filter digabungkan.

## 2. Backup, Export, dan Import Data Admin
Perbaiki fitur backup di halaman Admin. Saat ini muncul error:

`Database backup failed: 'mysqldump' is not recognized as an internal or external command, operable program or batch file.`

Ketentuan:
- Cari penyebab dan buat backup database yang berjalan di lingkungan project saat ini, termasuk Windows. Jangan mengandalkan `mysqldump` yang tidak tersedia tanpa penanganan yang tepat.
- Sediakan backup database dalam format **SQL**.
- Sediakan export data ke **Excel**.
- Data laporan harus dapat dirangkum menurut wilayah Jawa Barat, klaster, dan lingkungan, termasuk jumlah kelas anak serta data terkait yang memang tersedia di database.
- Tambahkan fitur **import Excel** untuk memasukkan atau memperbarui data seperti nama anak, wilayah, klaster, lingkungan, dan kolom lain yang didukung.
- Validasi format file, nama kolom, dan isi data sebelum import. Tampilkan hasil serta kesalahan import dengan jelas; hindari data ganda dan kerusakan data.
- Backup dan import harus aman, memiliki penanganan error, dan hanya dapat diakses oleh role yang berwenang.

## 3. Reset Password Admin
Pindahkan/letakkan fitur perubahan atau reset password Admin pada halaman **Profile Admin**. Jangan menampilkan tombol reset password sebagai fitur terpisah setelah Admin login di halaman lain. Pastikan proses perubahan password berfungsi dan aman.

## 4. Gambar Card Kegiatan
Gunakan gambar yang sudah tersedia di folder `public/images` untuk card kegiatan:
1. `1-belajar-bersama 1.png`
2. `2-bermain-kreatif 1.png`
3. `3-doa-dan-lagu 1`
4. `4-kunjungan-keluarga 1`

Periksa nama file dan ekstensi sebenarnya, terutama file nomor 3 dan 4. Gunakan path yang benar dan pastikan gambar tampil tanpa broken image.

## 5. Pengelolaan Card Beranda Publik dan Gambar Kelas Anak
- Tambahkan kemampuan Admin untuk mengelola card kegiatan anak yang tampil di beranda publik, termasuk data card dan gambarnya.
- Pada form tambah/edit data **Kelas Anak**, sediakan input upload gambar/foto yang sesuai dengan daerah atau kelas tersebut.
- Simpan gambar dengan mekanisme penyimpanan yang sesuai project, validasi tipe dan ukuran file, dan tampilkan gambar pada halaman terkait.
- Pertahankan data dan tampilan lama selama tidak bertentangan dengan kebutuhan ini.

## 6. Halaman Profile Admin dan Guru
Tambahkan halaman **Profile** pada navbar Dashboard Admin dan Dashboard Guru.

Gunakan desain dari:
- Referensi: `docs/design/screens/05-profile-settings.png`

Profile harus mengikuti UI referensi dan menampilkan informasi akun serta pengaturan yang relevan. Perubahan password Admin harus tersedia di Profile Admin. Pastikan route, navigasi, penyimpanan, dan hak akses berfungsi.

## 7. Aktifkan Fitur Dashboard Guru
Bangun dan pastikan fitur Dashboard Guru dapat digunakan, bukan hanya tampilan mockup. Gunakan referensi berikut:
- `docs/design/screens/04-dashboard-guru`
- `docs/design/screens/08-perkembangan-guru`
- `docs/design/screens/09-kegiatan-guru`
- `docs/design/screens/10-lesson-plan-guru-LAMA`
- `docs/design/screens/11-kunjungan-ortu-guru`

Implementasikan alur CRUD sesuai kebutuhan masing-masing halaman dan pola yang sudah ada, termasuk:
- Dashboard Guru: ringkasan data yang relevan.
- Perkembangan: pencatatan dan pengelolaan perkembangan/sikap anak.
- Kegiatan: pengelolaan kegiatan kelas.
- Lesson Plan: membuat, melihat, mengubah, dan menghapus rencana pembelajaran.
- Kunjungan Orang Tua: pencatatan dan pengelolaan kunjungan.
- Hubungkan setiap fitur dengan database dan pastikan data yang disimpan dapat ditampilkan kembali.
- Terapkan validasi, feedback sukses/gagal, relasi data yang benar, dan pembatasan akses agar Guru hanya mengelola data sesuai kewenangannya.
- Pertahankan gaya UI mockup dan konsistensi navbar.

## 8. Halaman Modul Pegangan Guru
Tambahkan satu halaman baru untuk **mengelola Modul Pegangan Guru Mengajar Kelas Anak** berdasarkan dokumen `MODUL PEGANGAN GURU MENGAJAR KELAS ANAK.pdf`.

Gunakan isi dokumen sebagai acuan. Bagian utama yang tampak dalam modul meliputi:
- Daftar kehadiran anak (nama, usia, dan kehadiran).
- Catatan ketidakhadiran, pelajaran yang tertinggal, dan alasan.
- Lesson Plan: tanggal, nama guru, tingkat, jumlah murid, tema, tujuan pembelajaran, waktu/program, serta alat dan peruntukannya.
- Refleksi Guru: pencapaian tujuan, pelaksanaan kegiatan, tantangan, dan pembelajaran yang diperoleh.
- Observasi kemajuan setiap murid.

Ketentuan:
- Buat halaman yang memudahkan Guru membuka dan mengelola modul tersebut.
- Sediakan fitur yang sesuai untuk melihat modul dan mengelola catatan terkait secara digital; hubungkan data yang dapat dikelola ke database.
- Pertahankan istilah dan struktur isi dari dokumen. Jangan mengarang materi ajar baru yang tidak terdapat di dokumen.
- Pastikan modul mudah diakses melalui navigasi Dashboard Guru dan tampil konsisten dengan desain project.
- Periksa isi PDF secara keseluruhan sebelum menentukan detail implementasi. Jika ada bagian yang belum jelas, pertahankan sesuai dokumen dan jangan menebak.

## 9. Pemeriksaan Akhir
Setelah implementasi:
1. Jalankan pemeriksaan route, migration/schema, relasi database, validasi, dan hak akses.
2. Uji CRUD setiap halaman Guru dan fitur Profile.
3. Uji filter Participants untuk region, klaster, dan lingkungan.
4. Uji backup SQL, export Excel, serta import Excel termasuk validasi file yang salah.
5. Uji upload dan tampilan gambar card kegiatan serta gambar Kelas Anak.
6. Pastikan tidak ada error baru, broken image, route yang tidak ditemukan, atau tombol yang tidak berfungsi.
7. Laporkan file yang diubah, fitur yang selesai, hasil pengujian, dan masalah yang masih tersisa. Jangan menyatakan fitur selesai jika belum diuji.
