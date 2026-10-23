<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class UserGuideController extends Controller
{
    public function admin(): View
    {
        return view('guides.index', [
            'title' => 'Panduan Admin',
            'description' => 'Langkah ringkas mengelola data dan layanan sistem.',
            'sections' => [
                ['title' => 'Mengelola akun Guru', 'steps' => ['Buka Teacher Accounts untuk membuat, memperbarui, menonaktifkan, atau mengaktifkan kembali akun Guru.', 'Gunakan reset kata sandi untuk mengeluarkan kata sandi sementara dan wajibkan Guru menggantinya saat masuk.']],
                ['title' => 'Mengelola wilayah dan kelas', 'steps' => ['Gunakan Regions, Cluster, Communities, dan Classes untuk merawat struktur wilayah.', 'Atur peserta melalui Participants dan lihat ringkasan pembelajaran pada Kelas.']],
                ['title' => 'Notifikasi dan chat', 'steps' => ['Buka Notifikasi untuk riwayat menurut tanggal.', 'Buka Inbox Bantuan untuk membalas pengunjung dan Guru; menghapus percakapan juga menghapus seluruh pesannya.']],
                ['title' => 'Backup dan ekspor data', 'steps' => ['Pilih jenis data dan filter wilayah di Database Backups untuk mengunduh Excel.', 'Backup SQL hanya tersedia untuk Admin dan disimpan privat; pemulihan dilakukan melalui Artisan CLI.']],
                ['title' => 'Kontak publik dan materi', 'steps' => ['Ubah nama, nomor telepon, dan avatar kontak publik melalui Pengaturan Kontak.', 'Kelola permintaan materi, aktivitas, dan kartu aktivitas publik melalui menu terkait.']],
            ],
        ]);
    }

    public function guru(): View
    {
        return view('guides.index', [
            'title' => 'Panduan Guru',
            'description' => 'Langkah ringkas untuk mengelola kelas dan dokumentasi kegiatan.',
            'sections' => [
                ['title' => 'Kelas dan peserta', 'steps' => ['Buka Classes untuk melihat kelas yang ditugaskan.', 'Gunakan Participants untuk mengelola peserta yang berada pada kelas yang ditugaskan.']],
                ['title' => 'Kehadiran dan perkembangan', 'steps' => ['Catat kehadiran peserta per tanggal dan sesi.', 'Perbarui Development dan Lesson Checklist setelah kegiatan.']],
                ['title' => 'Lesson Plan dan kegiatan', 'steps' => ['Buat Lesson Plan untuk kegiatan kelas atau rencana lainnya.', 'Catat kegiatan, refleksi, dan kunjungan orang tua pada menu masing-masing.']],
                ['title' => 'Cerita dan storyboard', 'steps' => ['Simpan atau sunting naskah pada Cerita & Ilustrasi lalu buat storyboard lokal tanpa API key.', 'Storyboard vektor disimpan privat dan dapat diunduh untuk presentasi. Jangan masukkan data pribadi anak.']],
                ['title' => 'Galeri kelas', 'steps' => ['Pilih kelas, tanggal, dan sesi saat mengunggah dokumentasi.', 'Galeri hanya dapat dilihat oleh Guru yang memiliki akses ke kelas tersebut.']],
                ['title' => 'Chat dan notifikasi', 'steps' => ['Hubungi Admin melalui Bantuan Admin dan hapus percakapan yang tidak diperlukan.', 'Buka Notifikasi untuk melihat pesan terbaru maupun riwayat sebelumnya.']],
                ['title' => 'Ekspor dan materi', 'steps' => ['Ekspor hanya memuat kelas dan peserta yang ditugaskan kepada Anda; berkasnya berupa Excel, bukan SQL.', 'Buka Draft Lagu & Materi untuk melihat bahan ajar yang tersedia.']],
            ],
        ]);
    }
}
