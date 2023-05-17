<p align="center"><a href="https://lavux.sopeus.com" target="_blank"><img src="https://lavux.sopeus.com/assets/logo.png" width="400" alt="Lavux Logo"></a></p>
<p align="center">
Laravel & Vue Enterprise Library <br>
</p>

Jika anda ingin melihat lebih detail bisa kunjungi [Documentation](https://lavux-doc.sopeus.com/)
atau Anda pensaran seperti apa lavux, coba lihat secara langsung dengan klik [Demo](https://lavux.sopeus.com/)


## About Lavux

Esensial komponen untuk membuat aplikasi enterprise secara efisien, konsisten, mudah & cepat.

Lavux dikembangkan menggunakan : 

> Backend
- Laravel versi 10 (PHP 8.1)

> Frontend
- Quasar Framework (Vue 3)


Mengadopsi konsep UI dengan Material Design yang sedikit di modifikasi, dan mengadopsi Konsep Repository Pattern untuk sisi Backend, semua data di olah menggunakan RESTfull API

## Lavux Page Components

Dilengkapi dengan standar fitur & komponen yang umum digunakan seperti :

- Input : (Text, Number, Currency, Textarea, File)

- Select : (Combo box, Auto Complete [static / dynamic / ajax ])

- Switcher : (Toggle , Checkbox)

- Uploader : (Raw file, Auto upload, Custom upload URL)

- Button : (Default, Soft)

- Table : (Static, Dynamic, Ajax)

---

Selain itu ada juga komponen standar lainnya yang dapat membantu Anda dalam membangun sebuah halaman agar lebih konsisten seperti : 

**Page Header**

mengatur header halaman lengkap dengan handler "previous page"


**Container**

Pembungkus komponen-komponen yang akan di lampirkan dihalaman yang sudah dilengkapi dengan kustom scroll


**SideMenu**

Menu navigasi bilah kiri, daftar menu diatur di API (Backend)


**TopMenu**

Menu navigasi pada bagian atas, Anda harus memilih salah satu desain menu yg Anda gunakan TopMenu / SideMenu


**TopBar**

Bar area di bagian atas yang mengakomodir sub menu khusus untuk halaman aplikasi, agar tidak terlalu banyak tombol action di bagian halaman maka dari itu TopBar kami sediakan, terinspirasi dari konsep menu di aplikasi Desktop


**ProfileChip**

Badge profil pengguna yang sudah di sisipkan di TopBar & TopMenu pada bagian kanan atas, memudahkan Anda untuk melihat informasi login Anda, berikut dengan jalan pintas untuk menuju profil Anda, terdapat pula pengaturan umum



---

## Default Modules

Dengan lavux Anda sudah bisa langsung membuat aplikasi secara instan & konsisten, karena semua kebutuhan Anda secara umum sudah terpenuh.

Anda sudah mendapatkan beberapa modul bawaan ketika Anda menginstall Lavux seperti : 

**Users**

Tersedia CRUD, halaman akun profil yang sudah dilengkapi dengan fitur memperbarui profil, ubah password & ganti foto profil (dengan fitur upload)


**Permissions**

Tersedia CRUD, modul untuk menyimpan daftar hak akses modul-modul yang ada


**Roles**

Tersedia CRUD, modul untuk menyimpan daftar  peran pengguna yang sudah terdefinisi hak aksesnya


**Role Groups**

Tersedia CRUD, modul untuk menyimpan grup peran untuk pengklasifikasian.


---

## App Access Controls

Fitur umum yang ada di sisi Front End :

**Action On Modal**

Memungkinkan Anda untuk mengakses halaman tertentu dalam sebuah modal tanpa harus redirect ke halaman tersebut


**Side / Top Menu Switcher**

Memungkinkan Anda untuk mengatur tata letak menu yang Anda inginkan, dibagian atas atau samping


**Reload Permissions**

Fitur untuk memuat ulang hak akses pengguna ketika ada pembaharuan dari sisi backend


**Handler Permission Page**

Halaman aplikasi yang tersedia sudah di lengkapi dengan handler hak akses, jadi kita bisa langsung mengatur tampilan terkait boleh / tidaknya halaman / fitur yang akan di akses (dari sisi backend juga ada handle hak akses)


**Table**

Data table yang sudah di desain secara khusus untuk mengakomodir kebutuhan manajemen data di tiap-tiap modul, dilengkapi dengan :

- Pencarian Global (bisa diatur ingin berdasarkan kolom tertentu)

- Pencarian Spesifik 
(berdasarkan kolom, lengkap dengan multi kondisi)

- Sortir ASC / DESC

- Kustom "slot"

- Virtual Scroll

- Seleksi data

- Pengaturan Tampilan Kolom


---

Fitur umum yang tersedia di sisi Backend

*Permission Handler*

Memungkinkan Anda untuk mengatur sumberdaya tertentu akan hak aksesnya, Anda bisa mengaturnya dari Controller, konsep hak akses disini mirip seperti Spatie, namun dibuat lebih ringkas.


