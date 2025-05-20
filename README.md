# Plugin SLiMS Storage Monitor

> ⚠️ **Peringatan**
> JANGAN langsung pasang DI SLiMS Operasional (tes di PC/SLiMS lain). Gunakan dengan risiko Anda sendiri.

SLiMS Storage Monitor adalah sebuah plugin untuk Senayan Library Management System (SLiMS) yang dirancang untuk menyediakan laporan mengenai penggunaan disk pada folder-folder penting SLiMS. Plugin ini membantu administrator memahami konsumsi penyimpanan, mengidentifikasi file atau plugin berukuran besar, dan memperkirakan keseluruhan ruang disk server.
![msedge_MgJ2zR8uxQ](https://github.com/user-attachments/assets/dae8b376-2f39-4c6e-aa8e-78c62985f86c)

## Fitur

*   **Penggunaan Disk Waktu-Nyata:** Melaporkan penggunaan disk saat ini untuk folder SLiMS yang dikonfigurasi.
*   **Tampilan Ringkasan:**
    *   Total ukuran dan jumlah item untuk setiap folder yang dipantau.
    *   Rincian jenis file teratas berdasarkan jumlah di setiap folder.
    *   Total keseluruhan untuk semua data SLiMS yang dipantau (file dan ukuran).
*   **Tampilan Detail Folder:**
    *   Menampilkan daftar semua file dan subfolder dalam path (jalur) yang dipantau dan dipilih.
    *   Menampilkan ukuran individual untuk setiap item.
    *   Hasil dengan paginasi untuk folder berukuran besar.
*   **Tampilan Item Teratas:** Menampilkan 5 file (atau plugin, untuk direktori `plugins`) terbesar untuk setiap path (jalur) yang dipantau dalam tata letak kartu di halaman ringkasan.
*   **Estimasi Ruang Disk Server:**
    *   Memperkirakan total ruang disk, yang terpakai, dan yang bebas untuk partisi tempat SLiMS diinstal.
    *   Bilah kemajuan visual yang menunjukkan persentase penggunaan.
*   **Path (Jalur) yang Dapat Dikonfigurasi:**
    *   Memantau sekumpulan folder SLiMS default (`repository`, `files/backup`, `files/reports`, `images/*`, `plugins`). Path (jalur) diambil relatif terhadap direktori root instalasi SLiMS.
    *   Memungkinkan penggantian path (jalur) default atau penambahan path (jalur) kustom baru melalui konfigurasi sistem SLiMS (`$sysconf['folder_size_report_paths']`).
*   **Penyaringan File:**
    *   Mengabaikan file sistem/web umum (misalnya, `index.php`, `.htaccess`, `.php`, `.env`, `.sh`) secara default untuk menyediakan statistik yang lebih relevan.
    *   Memungkinkan folder tertentu (misalnya, `files/reports`) untuk tetap menyertakan file HTML dalam pemindaian.
*   **Antarmuka yang Ramah Pengguna:**
    *   Terintegrasi ke dalam panel admin SLiMS di bawah "Pelaporan" ("Reporting") sebagai "Laporan Penyimpanan" ("Storage Report").
    *   Menggunakan gaya Bootstrap dan ikon FontAwesome untuk tampilan yang bersih dan modern.
    *   Menyediakan navigasi yang mudah antara tampilan ringkasan dan detail.

## Instalasi

baca di sini [https://github.com/adeism/belajarslims/blob/main/belajar-pasang-plugin.md](https://github.com/adeism/belajarslims/blob/main/belajar-pasang-plugin.md)

## Penggunaan

1.  Akses menu **Pelaporan > Monitor Penyimpanan** (Reporting > Storage Monitor) di panel admin SLiMS.
2.  **Halaman Ringkasan** akan menampilkan:
    *   Estimasi Ruang Disk Server (untuk partisi SLiMS).
    *   Tabel yang merangkum setiap folder yang dipantau: nama, path (jalur), jumlah item, total ukuran, dan jenis file teratas.
    *   Bagian dengan kartu, masing-masing menunjukkan 5 file/folder terbesar untuk path (jalur) yang dipantau.
3.  Klik nama folder di tabel ringkasan untuk melihat **Halaman Detail** folder tersebut. Halaman ini menampilkan semua konten (file dan sub-folder) beserta ukurannya, diurutkan berdasarkan ukuran, dan menyertakan paginasi jika ada banyak item.
4.  Gunakan tombol "Refresh" (Segarkan) untuk mendapatkan data terbaru.
5.  Gunakan tombol "Back to Summary" (Kembali ke Ringkasan) dari halaman detail untuk kembali ke laporan utama.
