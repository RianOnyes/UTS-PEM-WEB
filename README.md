Exploitasi dan Mitigasi SQL Injection pada Aplikasi Dompet Digital: Sebuah Studi Eksperimental

A.	Pendahuluan
Di era digital seperti sekarang, aplikasi dompet digital telah menjadi bagian tidak terpisahkan dari kehidupan sehari-hari. Mulai dari membayar tagihan, mentransfer uang, hingga membeli kebutuhan harian, semua dapat dilakukan hanya dengan beberapa ketukan pada layar ponsel. Namun, di balik kemudahan tersebut, tersembunyi risiko keamanan yang dapat mengancam data dan aset keuangan pengguna. Salah satu ancaman serius adalah SQL Injection (SQLi), sebuah teknik serangan yang telah ada selama bertahun-tahun namun tetap menjadi ancaman utama bagi aplikasi web dan mobile yang menggunakan database SQL.
Menurut laporan OWASP (Open Web Application Security Project) Top 10 tahun 2023, injeksi—termasuk SQL Injection—masih menempati peringkat tiga besar sebagai kerentanan keamanan web yang paling berbahaya. Hal ini menunjukkan bahwa meskipun telah banyak diketahui, banyak aplikasi masih rentan terhadap jenis serangan ini, termasuk aplikasi dompet digital yang mengelola informasi sensitif dan transaksi keuangan.
Artikel ini akan membahas secara mendalam tentang SQL Injection, bagaimana serangan ini dapat memengaruhi aplikasi dompet digital, dan langkah-langkah yang dapat diambil untuk mencegahnya. Saya juga akan membagikan hasil eksperimen yang telah saya lakukan pada sebuah prototype aplikasi dompet digital untuk mendemonstrasikan dampak dan cara mitigasi SQL Injection.

B.	Pembahasan Utama
1. Apa Itu SQL Injection
SQL Injection adalah teknik serangan dimana penyerang menyisipkan (inject) kode SQL berbahaya ke dalam query yang digunakan oleh aplikasi untuk berkomunikasi dengan database. Ketika aplikasi tidak memvalidasi atau membersihkan input pengguna dengan baik sebelum memasukkannya ke dalam query SQL, penyerang dapat memanipulasi query tersebut untuk mengakses, memodifikasi, atau menghapus data yang seharusnya tidak dapat mereka akses.
Bayangkan sebuah aplikasi dompet digital yang meminta pengguna memasukkan nomor telepon untuk melihat riwayat transaksi mereka. Di belakang layar, aplikasi mungkin menjalankan query SQL seperti:
SELECT * FROM transactions WHERE phone_number = '081234567890'
Jika aplikasi tidak memfilter input dengan benar, penyerang dapat memasukkan input berbahaya seperti:
081234567890' OR '1'='1

Query yang dihasilkan akan menjadi:
SELECT * FROM transactions WHERE phone_number = '081234567890' OR '1'='1'
Ekspresi '1'='1' selalu bernilai benar, sehingga query ini akan mengembalikan SEMUA catatan transaksi dalam database, bukan hanya milik pengguna tersebut.

Jenis-jenis SQL Injection
Dalam konteks aplikasi dompet digital, beberapa jenis SQL Injection yang umum adalah:
1.	Union-based SQLi: Penyerang menggunakan operator UNION untuk menggabungkan hasil query yang sah dengan hasil query berbahaya.
2.	Error-based SQLi: Memanfaatkan pesan error database untuk mendapatkan informasi tentang struktur database.
3.	Blind SQLi: Penyerang tidak mendapatkan hasil langsung dari query, tetapi dapat menyimpulkan informasi dari perilaku aplikasi.
4.	Time-based SQLi: Memanfaatkan fungsi penundaan database untuk menyimpulkan informasi berdasarkan waktu respons.
Eksperimen: SQL Injection pada Prototype Aplikasi Dompet Digital
Untuk memahami secara lebih mendalam dampak SQL Injection pada aplikasi dompet digital, saya telah melakukan serangkaian eksperimen pada sebuah prototype aplikasi dompet digital yang saya buat. Aplikasi ini memiliki fitur dasar seperti login, transfer uang, cek saldo, dan riwayat transaksi.
Setup Eksperimen
Teknologi yang digunakan:
•	Frontend: HTML, CSS, JavaScript
•	Backend: PHP
•	Database: MySQL
Struktur database sederhana terdiri dari tabel:
•	users: menyimpan informasi pengguna (id, username, password, full_name, balance)
•	transactions: menyimpan riwayat transaksi (id, sender_id, receiver_id, amount, timestamp)





Eksperimen 1: Authentication Bypass
Langkah pertama adalah menguji halaman login aplikasi untuk kerentanan SQL Injection. Halaman login menggunakan kode PHP berikut:
 
Eksekusi Serangan: Pada form login, saya memasukkan:
•	Nomor Telepon: ' OR '1'='1' --
•	Password: anything
Query yang dijalankan menjadi:
SELECT * FROM users WHERE phone_number = ' ' OR '1'='1' -- ' AND password = 'anything'
Karena karakter -- adalah komentar dalam SQL, bagian AND password = 'anything' menjadi tidak aktif. Kondisi '1'='1' selalu bernilai benar, sehingga query mengembalikan baris pertama dari tabel users, yang biasanya adalah admin.
Hasil: Saya berhasil login ke akun admin tanpa mengetahui password yang sebenarnya.



Eksperimen 2: Data Extraction
Setelah berhasil login, saya menguji fitur pencarian transaksi yang memungkinkan pengguna untuk mencari transaksi berdasarkan kata kunci.
Kode PHP untuk fitur pencarian:
 
Eksekusi Serangan: Pada form pencarian, saya memasukkan:
' UNION SELECT id, phone_number, password, balance, created_at FROM users WHERE '1'='1
Query yang dijalankan menjadi:
SELECT * FROM transactions WHERE description LIKE '%' UNION SELECT id, phone_number, password, balance, created_at FROM users WHERE '1'='1%'
Hasil: Aplikasi menampilkan seluruh data pengguna termasuk nomor telepon, password (yang tidak dienkripsi), dan saldo akun.
Eksperimen 3: Data Manipulation
Pada eksperimen ketiga, saya mencoba memanipulasi data saldo pengguna melalui fitur update profil.







Kode PHP untuk update profil:
 
Eksekusi Serangan: Pada form update profil, saya memasukkan:
•	Name: John', balance = 1000000 WHERE id = 2 --
•	Email: john@example.com

Query yang dijalankan menjadi:
UPDATE users SET full_name = 'John', balance = 1000000 WHERE id = 2 -- ', email = 'john@example.com' WHERE id = 1
Hasil: Berhasil mengubah saldo akun pengguna dengan ID 2 menjadi 1.000.000 tanpa melalui proses transfer yang sah.

Dampak SQL Injection pada Aplikasi Dompet Digital
Berdasarkan eksperimen yang dilakukan, dapat disimpulkan bahwa SQL Injection dapat memiliki dampak serius pada aplikasi dompet digital, antara lain:
1.	Kebocoran Data Sensitif: Penyerang dapat mengakses data pribadi pengguna seperti nama lengkap, nomor telepon, email, dan bahkan password jika disimpan tanpa enkripsi.
2.	Pencurian Dana: Melalui manipulasi database, penyerang dapat mengubah saldo akun atau membuat transaksi palsu.
3.	Pelanggaran Privasi: Riwayat transaksi pengguna dapat diakses, memberikan informasi tentang kebiasaan belanja dan kondisi keuangan.
4.	Kerusakan Reputasi: Keberhasilan serangan dapat menyebabkan kerugian reputasi yang signifikan bagi penyedia aplikasi dompet digital.
5.	Kerugian Finansial: Perusahaan mungkin harus menanggung kerugian finansial untuk mengganti dana pengguna yang hilang akibat serangan.

Kesimpulan
SQL Injection tetap menjadi ancaman serius bagi aplikasi dompet digital yang mengelola data sensitif dan transaksi keuangan. Eksperimen yang dilakukan dalam artikel ini menunjukkan betapa mudahnya bagi penyerang untuk mengeksploitasi kerentanan SQL Injection jika aplikasi tidak diimplementasikan dengan praktik keamanan yang tepat.
Keamanan aplikasi dompet digital harus menjadi prioritas utama, mengingat dampak serius yang dapat ditimbulkan oleh serangan SQL Injection. Implementasi teknik-teknik mitigasi seperti prepared statements, validasi input, dan prinsip hak akses minimum dapat secara signifikan mengurangi risiko.
Penting untuk diingat bahwa keamanan adalah proses berkelanjutan. Pengembang aplikasi dompet digital harus selalu mengikuti perkembangan terbaru dalam praktik keamanan web dan secara teratur melakukan pengujian penetrasi untuk mengidentifikasi dan memperbaiki kerentanan sebelum dieksploitasi oleh penyerang.
Dengan meningkatnya adopsi aplikasi dompet digital di Indonesia, keamanan tidak boleh dianggap sebagai fitur tambahan, melainkan komponen fundamental dalam pengembangan aplikasi yang dapat dipercaya oleh pengguna untuk mengelola keuangan mereka.
