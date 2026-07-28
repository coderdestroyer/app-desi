# Spesifikasi & Struktur Database Terintegrasi (PostgreSQL)
## Dashboard Executive Investment DPMPTSP + IPRO Project Calculation Engine

**Versi Database:** 2.0 (PostgreSQL Native Integration)  
**Database Engine:** PostgreSQL 15+  
**Standar Presisi Uang:** `NUMERIC(20, 2)`  
**Standar Waktu:** `TIMESTAMPTZ` (`TIMESTAMP WITH TIME ZONE`)  

---

## 1. Arsitektur Relasi Antar Domain Database

Database hasil penggabungan terdiri dari **6 Domain Utama** yang saling terintegrasi:

```mermaid
erDiagram
    users ||--o{ projects : "membuat/mengelola (Operator)"
    provinsi ||--o{ kabupaten : "memiliki"
    kabupaten ||--o{ kecamatan : "memiliki"
    kecamatan ||--o{ kelurahan_desa : "memiliki"
    kabupaten ||--o{ lokasi : "memiliki titik GIS"
    kabupaten ||--o{ pdrb_kabupaten : "memiliki data PDRB"
    sektor ||--o{ pdrb_kabupaten : "diklasifikasikan"
    
    kabupaten ||--o{ projects : "lokasi proyek IPRO"
    sektor ||--o{ projects : "sektor ekonomi IPRO"
    lokasi ||--o{ projects : "titik koordinat IPRO"
    
    projects ||--o{ capex_components : "memiliki rincian CAPEX"
    projects ||--o{ pl_components : "memiliki rincian P&L"
    pl_components ||--o{ pl_yearly_data : "memiliki nilai per tahun"
    
    capex_components ||--o{ capex_components : "parent-child (Adjacency List)"
    pl_components ||--o{ pl_components : "parent-child (Adjacency List)"
```

---

## 2. Struktur Tabel Detail (PostgreSQL DDL Specification)

### DOMAIN 1: Autentikasi, Keamanan & Pengguna

#### 1. Tabel `users`
Menampung data seluruh pengguna (Admin, Operator, dan User Terdaftar) beserta status verifikasi.

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    email_verified_at TIMESTAMPTZ NULL,
    password VARCHAR(255) NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user', -- 'admin', 'operator', 'user'
    status VARCHAR(20) NOT NULL DEFAULT 'approved', -- 'pending', 'approved', 'rejected'
    two_factor_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    google_id VARCHAR(255) NULL UNIQUE,
    avatar TEXT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);
```

#### 2. Tabel `activity_logs`
Pencatatan rekam jejak audit (*audit trail*) aktivitas admin dan operator.

```sql
CREATE TABLE activity_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

#### 3. Tabel `import_histories`
Pencatatan riwayat impor data PDRB/Analisis dari file Excel.

```sql
CREATE TABLE import_histories (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    file_name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'lq', 'ss', 'pdrb', etc.
    total_rows INT NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

### DOMAIN 2: Master Data Wilayah & Geografis (GIS)

#### 4. Tabel `provinsi`
```sql
CREATE TABLE provinsi (
    provinsi_id BIGSERIAL PRIMARY KEY,
    nama_provinsi VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

#### 5. Tabel `kabupaten`
```sql
CREATE TABLE kabupaten (
    kab_id BIGSERIAL PRIMARY KEY,
    provinsi_id BIGINT NOT NULL REFERENCES provinsi(provinsi_id) ON DELETE CASCADE ON UPDATE CASCADE,
    nama_kabupaten VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_kabupaten_provinsi ON kabupaten(provinsi_id);
```

#### 6. Tabel `kecamatan`
```sql
CREATE TABLE kecamatan (
    id BIGSERIAL PRIMARY KEY,
    kabupaten_id BIGINT NOT NULL REFERENCES kabupaten(kab_id) ON DELETE CASCADE,
    nama_kecamatan VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

#### 7. Tabel `kelurahan_desa`
```sql
CREATE TABLE kelurahan_desa (
    id BIGSERIAL PRIMARY KEY,
    kecamatan_id BIGINT NOT NULL REFERENCES kecamatan(id) ON DELETE CASCADE,
    nama_desa VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

#### 8. Tabel `lokasi` (GIS Coordinates)
Menyimpan titik sampel/koordinat geografis potensi investasi untuk visualisasi Leaflet.js.

```sql
CREATE TABLE lokasi (
    id BIGSERIAL PRIMARY KEY,
    kabupaten_id BIGINT NULL REFERENCES kabupaten(kab_id) ON DELETE SET NULL,
    nama VARCHAR(255) NOT NULL,
    latitude NUMERIC(10, 8) NOT NULL,
    longitude NUMERIC(11, 8) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

### DOMAIN 3: Klasifikasi Standar (Sektor, KBLI, KBKI, HS Code)

#### 9. Tabel `sektor`
```sql
CREATE TABLE sektor (
    sektor_id BIGSERIAL PRIMARY KEY,
    nama_sektor VARCHAR(255) NOT NULL
);
```

#### 10. Tabel `data_kbli`
Klasifikasi Baku Lapangan Usaha Indonesia (KBLI 5 Digit) dengan self-referencing `kode_induk`.

```sql
CREATE TABLE data_kbli (
    id BIGSERIAL PRIMARY KEY,
    struktur VARCHAR(20) NOT NULL,
    level SMALLINT NOT NULL,
    kode VARCHAR(10) NOT NULL UNIQUE,
    kode_induk VARCHAR(10) NULL,
    kategori_kode VARCHAR(2) NULL,
    judul TEXT NOT NULL,
    cakupan TEXT NULL,
    tidak_cakupan TEXT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_kbli_kode_induk ON data_kbli(kode_induk);
```

#### 11. Tabel `data_kbki`
Klasifikasi Baku Komoditas Indonesia.

```sql
CREATE TABLE data_kbki (
    id BIGSERIAL PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    kode_induk VARCHAR(20) NULL,
    level SMALLINT NOT NULL,
    nama TEXT NOT NULL,
    deskripsi TEXT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

#### 12. Tabel `data_hs_code`
Harmonized System Code untuk ekspor-impor.

```sql
CREATE TABLE data_hs_code (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    category VARCHAR(255) NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

### DOMAIN 4: Data Ekonomi Makro & PDRB

#### 13. Tabel `pdrb_sumut` (Daerah Pembanding Acuan)
```sql
CREATE TABLE pdrb_sumut (
    id BIGSERIAL PRIMARY KEY,
    sektor_id BIGINT NOT NULL REFERENCES sektor(sektor_id) ON DELETE CASCADE,
    tahun INT NOT NULL,
    nilai_pdrb NUMERIC(20, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

#### 14. Tabel `pdrb_kabupaten` (Daerah Analisis)
```sql
CREATE TABLE pdrb_kabupaten (
    id BIGSERIAL PRIMARY KEY,
    kabupaten_id BIGINT NOT NULL REFERENCES kabupaten(kab_id) ON DELETE CASCADE,
    sektor_id BIGINT NOT NULL REFERENCES sektor(sektor_id) ON DELETE CASCADE,
    tahun INT NOT NULL,
    nilai_pdrb NUMERIC(20, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

### DOMAIN 5: Output Analisis Makroekonomi

#### 15. Tabel `analisis_lq`
```sql
CREATE TABLE analisis_lq (
    id BIGSERIAL PRIMARY KEY,
    tingkat_wilayah VARCHAR(50) NOT NULL,
    daerah_analisis VARCHAR(255) NOT NULL,
    daerah_pembanding VARCHAR(255) NOT NULL,
    sektor_id BIGINT NOT NULL REFERENCES sektor(sektor_id) ON DELETE CASCADE,
    tahun INT NOT NULL,
    pdrb_sektor_analisis NUMERIC(20, 2) NOT NULL,
    total_pdrb_analisis NUMERIC(20, 2) NOT NULL,
    pdrb_sektor_pembanding NUMERIC(20, 2) NOT NULL,
    total_pdrb_pembanding NUMERIC(20, 2) NOT NULL,
    nilai_lq NUMERIC(10, 4) NOT NULL,
    kategori VARCHAR(50) NOT NULL, -- 'Basis' / 'Non-Basis'
    keterangan TEXT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

#### 16. Tabel `analisis_ss` (Shift-Share Analysis)
```sql
CREATE TABLE analisis_ss (
    id BIGSERIAL PRIMARY KEY,
    kabupaten_id BIGINT NOT NULL REFERENCES kabupaten(kab_id) ON DELETE CASCADE,
    sektor_id BIGINT NOT NULL REFERENCES sektor(sektor_id) ON DELETE CASCADE,
    tahun_awal INT NOT NULL,
    tahun_akhir INT NOT NULL,
    komponen_n NUMERIC(20, 2) NOT NULL, -- Regional Growth
    komponen_p NUMERIC(20, 2) NOT NULL, -- Proportional Shift
    komponen_d NUMERIC(20, 2) NOT NULL, -- Differential Shift
    total_shift NUMERIC(20, 2) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

#### 17. Tabel `analisis_tipologi` (Matriks Tipologi Sektor)
```sql
CREATE TABLE analisis_tipologi (
    id BIGSERIAL PRIMARY KEY,
    kabupaten_id BIGINT NOT NULL REFERENCES kabupaten(kab_id) ON DELETE CASCADE,
    sektor_id BIGINT NOT NULL REFERENCES sektor(sektor_id) ON DELETE CASCADE,
    tahun INT NOT NULL,
    kuadran VARCHAR(20) NOT NULL, -- 'Kuadran I', 'Kuadran II', 'Kuadran III', 'Kuadran IV'
    kategori_sektor VARCHAR(100) NOT NULL, -- 'Sektor Prima', 'Sektor Berkembang', etc.
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

#### 18. Tabel `analisis_klassen` (Tipologi Klassen Daerah/Sektor)
```sql
CREATE TABLE analisis_klassen (
    id BIGSERIAL PRIMARY KEY,
    kabupaten_id BIGINT NOT NULL REFERENCES kabupaten(kab_id) ON DELETE CASCADE,
    sektor_id BIGINT NOT NULL REFERENCES sektor(sektor_id) ON DELETE CASCADE,
    laju_pertumbuhan NUMERIC(8, 4) NOT NULL,
    kontribusi_pdrb NUMERIC(8, 4) NOT NULL,
    kuadran VARCHAR(20) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

### DOMAIN 6: Mikrosistem Kalkulasi Finansial Proyek (IPRO Engine)

#### 19. Tabel `projects` (Header Dokumen IPRO)
Koreksi & Penggabungan: Ditambahkan Foreign Key ke `kabupaten_id`, `sektor_id`, dan `lokasi_id` untuk menghubungkan proyek IPRO dengan Peta GIS & Potensi Daerah DPMPTSP.

```sql
CREATE TABLE projects (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    kabupaten_id BIGINT NULL REFERENCES kabupaten(kab_id) ON DELETE SET NULL,
    sektor_id BIGINT NULL REFERENCES sektor(sektor_id) ON DELETE SET NULL,
    lokasi_id BIGINT NULL REFERENCES lokasi(id) ON DELETE SET NULL,
    nama_proyek VARCHAR(255) NOT NULL,
    deskripsi TEXT NULL,
    tahun_awal INT NOT NULL DEFAULT 2026,
    jangka_waktu_tahun INT NOT NULL DEFAULT 10,
    status_publikasi VARCHAR(20) NOT NULL DEFAULT 'published', -- 'draft', 'published'
    
    -- Pengaturan Finansial P&L
    pl_persentase_pajak_penghasilan NUMERIC(5, 2) NOT NULL DEFAULT 0.00,
    pl_persentase_pajak_daerah NUMERIC(5, 2) NOT NULL DEFAULT 0.00,
    pl_persentase_bot_bgs_fee NUMERIC(5, 2) NOT NULL DEFAULT 0.00,
    pl_nominal_bunga NUMERIC(20, 2) NOT NULL DEFAULT 0.00,
    pl_nominal_depresiasi NUMERIC(20, 2) NOT NULL DEFAULT 0.00,
    
    -- Pengaturan Skema Pembiayaan Kredit & Cashflow
    rasio_modal_sendiri NUMERIC(5, 2) NOT NULL DEFAULT 60.00, -- Equity %
    rasio_pinjaman_kredit NUMERIC(5, 2) NOT NULL DEFAULT 40.00, -- Debt %
    suku_bunga_kredit NUMERIC(5, 2) NOT NULL DEFAULT 8.05, -- Bunga % p.a.
    tenor_kredit_tahun INT NOT NULL DEFAULT 5, -- Tenor Pinjaman
    
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_projects_kabupaten ON projects(kabupaten_id);
CREATE INDEX idx_projects_sektor ON projects(sektor_id);
```

#### 20. Tabel `capex_components` (Struktur Hirarki CAPEX)
Menggunakan pendekatan *Adjacency List* (`parent_id`) untuk hierarki dinamis Kategori $\rightarrow$ Subkategori tanpa batasan level.

```sql
CREATE TABLE capex_components (
    id BIGSERIAL PRIMARY KEY,
    project_id BIGINT NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    parent_id BIGINT NULL REFERENCES capex_components(id) ON DELETE CASCADE,
    nama_komponen VARCHAR(255) NOT NULL,
    volume NUMERIC(15, 2) NULL,
    satuan VARCHAR(50) NULL, -- 'unit', 'm2', 'ls', 'set'
    luas NUMERIC(15, 2) NULL,
    harga_m2 NUMERIC(20, 2) NULL, -- Harga Satuan
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_capex_project ON capex_components(project_id);
CREATE INDEX idx_capex_parent ON capex_components(parent_id);
```

#### 21. Tabel `pl_components` (Header Komponen P&L)
```sql
CREATE TABLE pl_components (
    id BIGSERIAL PRIMARY KEY,
    project_id BIGINT NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    tipe_kategori VARCHAR(30) NOT NULL, -- 'PENDAPATAN', 'BIAYA_OPERASIONAL'
    parent_id BIGINT NULL REFERENCES pl_components(id) ON DELETE CASCADE,
    nama_komponen VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_pl_comp_project ON pl_components(project_id);
CREATE INDEX idx_pl_comp_parent ON pl_components(parent_id);
```

#### 22. Tabel `pl_yearly_data` (Nilai Proyeksi P&L Tahunan)
```sql
CREATE TABLE pl_yearly_data (
    id BIGSERIAL PRIMARY KEY,
    pl_component_id BIGINT NOT NULL REFERENCES pl_components(id) ON DELETE CASCADE,
    tahun_ke INT NOT NULL, -- 1, 2, 3, dst.
    nilai NUMERIC(20, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT unq_pl_yearly UNIQUE(pl_component_id, tahun_ke)
);
```

> [!NOTE]
> **Arus Kas (Cash Flow)** dikalkulasi secara **100% dinamis (real-time)** dari penggabungan `capex_components`, parameter kredit `projects` (Equity/Debt 60:40, Suku Bunga 8.05%, Tenor), dan `pl_yearly_data` sehingga tidak memerlukan penyimpanan tabel fisik tambahan.
