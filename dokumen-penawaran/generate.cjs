const fs = require("fs");
const {
  Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType,
  Table, TableRow, TableCell, WidthType, BorderStyle, ShadingType,
  PageBreak, LevelFormat, Footer, PositionalTab,
  PositionalTabAlignment, PositionalTabLeader, VerticalAlign,
} = require("docx");

// ---- Brand ----
const BLUE = "1D4ED8";
const BLUE_DARK = "1E3A8A";
const SLATE = "334155";
const SLATE_LIGHT = "64748B";
const HEAD_FILL = "1E3A8A";
const ZEBRA = "EEF2FF";
const LINE = "CBD5E1";
const FONT = "Calibri";

const CONTENT_W = 9026; // A4 content width in DXA (portrait, ~1in margins)

// ---- Helpers ----
const t = (text, opts = {}) => new TextRun({ text, font: FONT, ...opts });

function h1(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    spacing: { before: 320, after: 140 },
    children: [new TextRun({ text, font: FONT, bold: true, color: BLUE_DARK, size: 30 })],
  });
}
function h2(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    spacing: { before: 220, after: 100 },
    children: [new TextRun({ text, font: FONT, bold: true, color: BLUE, size: 24 })],
  });
}
function p(text, opts = {}) {
  const runs = Array.isArray(text) ? text : [t(text, { size: 21, color: "1F2937" })];
  return new Paragraph({ spacing: { after: 120, line: 276 }, alignment: opts.align, children: runs, ...opts.para });
}
function bullet(text, level = 0) {
  const runs = Array.isArray(text) ? text : [t(text, { size: 21, color: "1F2937" })];
  return new Paragraph({ numbering: { reference: "bullets", level }, spacing: { after: 60, line: 264 }, children: runs });
}
function num(text, ref = "steps") {
  const runs = Array.isArray(text) ? text : [t(text, { size: 21, color: "1F2937" })];
  return new Paragraph({ numbering: { reference: ref, level: 0 }, spacing: { after: 60, line: 264 }, children: runs });
}
function spacer(after = 120) { return new Paragraph({ spacing: { after }, children: [t("")] }); }

function rule() {
  return new Paragraph({
    spacing: { before: 80, after: 160 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: BLUE } },
    children: [t("")],
  });
}

const noBorders = {
  top: { style: BorderStyle.NONE, size: 0, color: "FFFFFF" },
  bottom: { style: BorderStyle.NONE, size: 0, color: "FFFFFF" },
  left: { style: BorderStyle.NONE, size: 0, color: "FFFFFF" },
  right: { style: BorderStyle.NONE, size: 0, color: "FFFFFF" },
  insideHorizontal: { style: BorderStyle.NONE, size: 0, color: "FFFFFF" },
  insideVertical: { style: BorderStyle.NONE, size: 0, color: "FFFFFF" },
};
const thinBorders = {
  top: { style: BorderStyle.SINGLE, size: 4, color: LINE },
  bottom: { style: BorderStyle.SINGLE, size: 4, color: LINE },
  left: { style: BorderStyle.SINGLE, size: 4, color: LINE },
  right: { style: BorderStyle.SINGLE, size: 4, color: LINE },
  insideHorizontal: { style: BorderStyle.SINGLE, size: 4, color: LINE },
  insideVertical: { style: BorderStyle.SINGLE, size: 4, color: LINE },
};

function cell(content, { widthDxa, fill, bold, color, size = 20, align = AlignmentType.LEFT, valign = VerticalAlign.CENTER } = {}) {
  const paras = (Array.isArray(content) ? content : [content]).map((line) =>
    new Paragraph({
      alignment: align,
      spacing: { after: 40, before: 40, line: 252 },
      children: [new TextRun({ text: line, font: FONT, bold: !!bold, color: color || "1F2937", size })],
    })
  );
  return new TableCell({
    width: { size: widthDxa, type: WidthType.DXA },
    margins: { top: 60, bottom: 60, left: 110, right: 110 },
    shading: fill ? { type: ShadingType.CLEAR, fill, color: "auto" } : undefined,
    verticalAlign: valign,
    children: paras,
  });
}

function makeTable(headers, rows, weights, opts = {}) {
  const totalW = opts.width || CONTENT_W;
  const sum = weights.reduce((a, b) => a + b, 0);
  const widths = weights.map((w) => Math.round((w / sum) * totalW));
  const drift = totalW - widths.reduce((a, b) => a + b, 0);
  widths[widths.length - 1] += drift;

  const headerRow = new TableRow({
    tableHeader: true,
    children: headers.map((hd, i) =>
      cell(hd, { widthDxa: widths[i], fill: HEAD_FILL, bold: true, color: "FFFFFF", size: 20, align: opts.headAlign ? opts.headAlign[i] : AlignmentType.LEFT })
    ),
  });
  const bodyRows = rows.map((r, ri) =>
    new TableRow({
      children: r.map((cc, i) =>
        cell(cc, {
          widthDxa: widths[i],
          fill: ri % 2 === 1 ? ZEBRA : undefined,
          bold: opts.boldCol && opts.boldCol.includes(i),
          size: 20,
          align: opts.align ? opts.align[i] : AlignmentType.LEFT,
        })
      ),
    })
  );
  return new Table({
    columnWidths: widths,
    width: { size: totalW, type: WidthType.DXA },
    borders: thinBorders,
    rows: [headerRow, ...bodyRows],
  });
}

function metaBox(pairs) {
  const kW = 2600, vW = CONTENT_W - kW;
  const rows = pairs.map(([k, v], i) =>
    new TableRow({
      children: [
        cell(k, { widthDxa: kW, fill: i % 2 === 1 ? ZEBRA : "F8FAFC", bold: true, color: SLATE, size: 20 }),
        cell(v, { widthDxa: vW, fill: i % 2 === 1 ? ZEBRA : "F8FAFC", size: 20 }),
      ],
    })
  );
  return new Table({ columnWidths: [kW, vW], width: { size: CONTENT_W, type: WidthType.DXA }, borders: thinBorders, rows });
}

function coverBlock(kicker, title, subtitle, docType, pairs) {
  return [
    spacer(600),
    new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 80 },
      children: [new TextRun({ text: kicker, font: FONT, bold: true, color: BLUE, size: 22, allCaps: true })] }),
    new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 40 },
      children: [new TextRun({ text: title, font: FONT, bold: true, color: BLUE_DARK, size: 72 })] }),
    new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 240 },
      children: [new TextRun({ text: subtitle, font: FONT, color: SLATE, size: 26 })] }),
    new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 200, after: 60 },
      border: { top: { style: BorderStyle.SINGLE, size: 6, color: BLUE }, bottom: { style: BorderStyle.SINGLE, size: 6, color: BLUE } },
      children: [new TextRun({ text: docType, font: FONT, bold: true, color: BLUE, size: 30 })] }),
    spacer(600),
    metaBox(pairs),
    spacer(300),
    new Paragraph({ alignment: AlignmentType.CENTER,
      children: [new TextRun({ text: "Dokumen ini bersifat rahasia dan ditujukan khusus bagi penerima yang tercantum di atas.", font: FONT, italics: true, color: SLATE_LIGHT, size: 18 })] }),
    new Paragraph({ children: [new PageBreak()] }),
  ];
}

const numbering = {
  config: [
    {
      reference: "bullets",
      levels: [
        { level: 0, format: LevelFormat.BULLET, text: "•", alignment: AlignmentType.LEFT,
          style: { run: { color: BLUE }, paragraph: { indent: { left: 460, hanging: 260 } } } },
        { level: 1, format: LevelFormat.BULLET, text: "◦", alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 900, hanging: 260 } } } },
      ],
    },
    {
      reference: "steps",
      levels: [
        { level: 0, format: LevelFormat.DECIMAL, text: "%1.", alignment: AlignmentType.LEFT,
          style: { run: { bold: true, color: BLUE }, paragraph: { indent: { left: 460, hanging: 320 } } } },
      ],
    },
    {
      reference: "clauses",
      levels: [
        { level: 0, format: LevelFormat.DECIMAL, text: "(%1)", alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 520, hanging: 360 } } } },
      ],
    },
  ],
};

function footer(docName) {
  return new Footer({
    children: [
      new Paragraph({
        border: { top: { style: BorderStyle.SINGLE, size: 4, color: LINE } },
        tabStops: [{ type: "right", position: CONTENT_W }],
        children: [
          new TextRun({ text: docName, font: FONT, color: SLATE_LIGHT, size: 16 }),
          new TextRun({ text: "\t", font: FONT }),
          new TextRun({ text: "SIMAC • Rahasia", font: FONT, color: SLATE_LIGHT, size: 16 }),
        ],
      }),
    ],
  });
}

function baseDoc(children, footerName) {
  return new Document({
    creator: "SIMAC",
    title: footerName,
    numbering,
    styles: { default: { document: { run: { font: FONT } } } },
    sections: [{
      properties: { page: { margin: { top: 1440, bottom: 1440, left: 1440, right: 1440 } } },
      footers: { default: footer(footerName) },
      children,
    }],
  });
}

function callout(title, lines) {
  const rows = [
    new TableRow({ children: [ cell(title, { widthDxa: CONTENT_W, fill: BLUE, bold: true, color: "FFFFFF", size: 22 }) ] }),
    new TableRow({ children: [ new TableCell({
      width: { size: CONTENT_W, type: WidthType.DXA },
      margins: { top: 120, bottom: 120, left: 160, right: 160 },
      shading: { type: ShadingType.CLEAR, fill: "F8FAFC", color: "auto" },
      children: lines.map((l) => new Paragraph({ spacing: { after: 60, line: 264 },
        children: [ new TextRun({ text: "✓  ", font: FONT, bold: true, color: BLUE }), new TextRun({ text: l, font: FONT, size: 21, color: "1F2937" }) ] })),
    }) ] }),
  ];
  return new Table({ columnWidths: [CONTENT_W], width: { size: CONTENT_W, type: WidthType.DXA }, borders: noBorders, rows });
}

/* =====================================================================
   DOCUMENT 1 — PROPOSAL PENAWARAN PRODUK
   ===================================================================== */
function buildProposal() {
  const c = [];
  c.push(...coverBlock(
    "Proposal Penawaran", "SIMAC", "Sistem Manajemen Layanan Maintenance AC",
    "PROPOSAL PENAWARAN PRODUK",
    [
      ["Disiapkan untuk", "[Nama Calon Klien / Perusahaan]"],
      ["Disiapkan oleh", "[Nama Anda / Nama Vendor]"],
      ["Kontak", "[No. HP / WhatsApp] • [Email]"],
      ["Tanggal", "24 Agustus 2026"],
      ["Versi Dokumen", "1.0"],
    ]
  ));

  c.push(h1("1. Ringkasan Eksekutif"));
  c.push(p("SIMAC (Sistem Manajemen Layanan Maintenance AC) adalah aplikasi web siap pakai yang dirancang khusus untuk mengelola seluruh operasional bisnis jasa perawatan dan perbaikan AC — mulai dari pendataan pelanggan dan unit AC, pembuatan pesanan servis (booking), penugasan serta pelacakan status teknisi di lapangan, pencatatan pembayaran dan ulasan, hingga laporan pendapatan untuk pemilik usaha."));
  c.push(p("Dengan SIMAC, pengelolaan yang selama ini dilakukan lewat catatan manual, WhatsApp, dan spreadsheet dapat dipusatkan dalam satu sistem yang rapi, dapat diakses dari mana saja, dan mudah dipakai baik di komputer maupun ponsel. Hasilnya: pekerjaan lebih terkontrol, pelanggan lebih puas, dan pemilik memiliki gambaran bisnis yang jelas setiap saat."));
  c.push(callout("Nilai Utama untuk Bisnis Anda", [
    "Semua data pelanggan, unit AC, dan riwayat servis tersimpan rapi di satu tempat.",
    "Penjadwalan dan penugasan teknisi lebih cepat, jelas, dan terlacak.",
    "Pengingat servis berkala otomatis — peluang pendapatan berulang tidak terlewat.",
    "Laporan pendapatan dan performa teknisi siap pakai untuk pengambilan keputusan.",
    "Bisa diakses seperti aplikasi di HP (installable / PWA) tanpa perlu unduh dari toko aplikasi.",
  ]));

  c.push(h1("2. Tentang SIMAC"));
  c.push(p("SIMAC dibangun di atas teknologi web modern dan telah mencakup alur kerja sehari-hari sebuah usaha jasa AC secara menyeluruh. Aplikasi ini mendukung tiga peran pengguna dengan hak akses yang berbeda, sehingga setiap orang hanya melihat dan mengerjakan bagian yang menjadi tanggung jawabnya."));
  c.push(makeTable(
    ["Peran", "Akses & Tanggung Jawab"],
    [
      ["Admin", "Mengelola pelanggan, unit AC, layanan, dan booking harian; menugaskan teknisi; mencatat pembayaran & ulasan; mengelola pengguna."],
      ["Owner / Manajer", "Melihat dashboard, laporan pendapatan, dan performa teknisi (mayoritas hanya-baca) untuk pemantauan bisnis."],
      ["Teknisi", "Melihat daftar tugas, memperbarui status pekerjaan di lapangan, dan meninjau riwayat tugas yang telah selesai."],
    ],
    [2, 6]
  ));
  c.push(spacer(80));
  c.push(p([t("Catatan: ", { bold: true, size: 20, color: SLATE }), t("pelanggan tidak memerlukan akun — seluruh datanya dikelola oleh Admin, sehingga operasional tetap sederhana.", { size: 20, italics: true, color: SLATE })]));

  c.push(h1("3. Tantangan yang Umum Dihadapi Bisnis Jasa AC"));
  c.push(p("Tanpa sistem yang terpusat, usaha jasa AC sering menghadapi kendala berikut:"));
  c.push(bullet("Data pelanggan dan riwayat servis tersebar di banyak catatan, sulit dicari saat dibutuhkan."));
  c.push(bullet("Penjadwalan dan penugasan teknisi tumpang tindih atau terlewat."));
  c.push(bullet("Sulit mengetahui status pekerjaan yang sedang berjalan secara real-time."));
  c.push(bullet("Pengingat servis rutin bergantung pada ingatan, sehingga banyak peluang pendapatan berulang hilang."));
  c.push(bullet("Pemilik tidak memiliki laporan pendapatan dan performa yang akurat untuk mengambil keputusan."));

  c.push(h1("4. Bagaimana SIMAC Menyelesaikannya"));
  c.push(makeTable(
    ["Tantangan", "Solusi SIMAC"],
    [
      ["Data tersebar & sulit dicari", "Basis data terpusat dengan pencarian cepat di setiap daftar (booking, pelanggan, pengguna)."],
      ["Penjadwalan & penugasan berantakan", "Alur booking terstruktur dengan penugasan teknisi yang tersedia dan pelacakan status."],
      ["Status pekerjaan tidak jelas", "Status pekerjaan berpindah bertahap dan tercatat lengkap dalam riwayat (audit trail)."],
      ["Servis berkala terlewat", "Dashboard menandai unit yang servis terakhirnya >90 hari + tombol jadwalkan ulang."],
      ["Tidak ada laporan bisnis", "Laporan pendapatan & performa teknisi per rentang tanggal untuk Owner."],
    ],
    [4, 6]
  ));

  c.push(new Paragraph({ children: [new PageBreak()] }));
  c.push(h1("5. Fitur Unggulan"));
  c.push(h2("Operasional"));
  c.push(makeTable(
    ["Fitur", "Manfaat"],
    [
      ["Manajemen Pelanggan & Unit AC", "Simpan data pelanggan lengkap dengan daftar unit AC (merek, tipe, kapasitas PK, lokasi) agar teknisi siap di lapangan."],
      ["Booking Servis", "Buat pesanan servis dengan memilih pelanggan, unit, layanan, dan jadwal. Pelanggan/unit baru bisa langsung didaftarkan tanpa kehilangan konteks."],
      ["Penugasan & Status Teknisi", "Tugaskan teknisi yang tersedia dan pantau perpindahan status pekerjaan; setiap perubahan tercatat."],
      ["Pembayaran & Ulasan", "Catat pembayaran dan kumpulkan ulasan pelanggan setelah servis selesai."],
      ["Pengingat Servis Berkala", "Sistem menandai unit yang perlu diservis ulang dan menyediakan tombol untuk menjadwalkannya."],
      ["Notifikasi WhatsApp", "Kirim pesan otomatis ke pelanggan (konfirmasi, pengingat jadwal, servis selesai) via wa.me sekali klik."],
      ["Laporan", "Ringkasan pendapatan dan performa teknisi per periode untuk kebutuhan manajemen."],
    ],
    [4, 6]
  ));
  c.push(spacer(80));
  c.push(h2("Pengalaman Pengguna"));
  c.push(bullet([t("Responsif / mobile-friendly", { bold: true, size: 21, color: SLATE }), t(" — di HP tampil seperti aplikasi (bottom navigation), tabel berubah menjadi kartu; di desktop tampil penuh dengan sidebar.", { size: 21, color: "1F2937" })]));
  c.push(bullet([t("PWA (installable)", { bold: true, size: 21, color: SLATE }), t(" — dapat dipasang ke home screen HP dan berjalan layaknya aplikasi native, lengkap dengan halaman fallback saat offline.", { size: 21, color: "1F2937" })]));
  c.push(bullet([t("Pencarian di setiap daftar", { bold: true, size: 21, color: SLATE }), t(" — cari cepat dan gabungkan dengan filter status.", { size: 21, color: "1F2937" })]));
  c.push(bullet([t("Panduan pengguna per halaman", { bold: true, size: 21, color: SLATE }), t(" — panel bantuan yang bisa dibuka/tutup di tiap halaman.", { size: 21, color: "1F2937" })]));
  c.push(bullet([t("Tombol berwarna jelas & identitas visual konsisten", { bold: true, size: 21, color: SLATE }), t(" — mengurangi risiko salah klik.", { size: 21, color: "1F2937" })]));

  c.push(h1("6. Keunggulan Teknologi & Keamanan"));
  c.push(makeTable(
    ["Komponen", "Teknologi"],
    [
      ["Backend", "Laravel 13 (PHP 8.3+) — framework matang & banyak dipakai industri"],
      ["Frontend", "Blade + Bootstrap 5 — ringan, cepat, dan responsif"],
      ["Database", "MySQL 8"],
      ["Deployment", "Docker Compose — mudah dijalankan di server mana pun"],
      ["Keamanan", "Autentikasi bawaan Laravel, password ter-enkripsi (hash), hak akses per peran, reset password via email"],
    ],
    [3, 7]
  ));
  c.push(spacer(80));
  c.push(p("Arsitektur yang standar dan populer membuat SIMAC mudah dirawat, dikembangkan, dan dialihkan ke tim lain di masa depan — melindungi investasi Anda dalam jangka panjang."));

  c.push(h1("7. Manfaat untuk Bisnis Anda"));
  c.push(makeTable(
    ["Aspek", "Sebelum SIMAC", "Dengan SIMAC"],
    [
      ["Data pelanggan", "Tersebar di buku/WA/spreadsheet", "Terpusat, mudah dicari"],
      ["Penjadwalan", "Manual, rawan bentrok", "Terstruktur & terlacak"],
      ["Servis berkala", "Mengandalkan ingatan", "Pengingat otomatis"],
      ["Komunikasi pelanggan", "Ketik manual satu per satu", "Pesan WhatsApp otomatis"],
      ["Laporan bisnis", "Sulit & memakan waktu", "Siap pakai per periode"],
    ],
    [3, 4, 4]
  ));
  c.push(spacer(80));
  c.push(p("Dampak akhirnya: waktu administrasi berkurang, lebih sedikit pekerjaan yang terlewat, pengalaman pelanggan meningkat, dan pemilik memegang kendali penuh atas kesehatan bisnisnya."));

  c.push(h1("8. Demo Produk"));
  c.push(p("Kami dengan senang hati memberikan demo langsung agar Anda dapat mencoba SIMAC sebelum memutuskan. Sesi demo mencakup alur lengkap dari sisi Admin, Owner, dan Teknisi, serta tanya-jawab sesuai kebutuhan bisnis Anda."));
  c.push(p([t("Untuk menjadwalkan demo, silakan hubungi kami melalui kontak pada halaman terakhir dokumen ini.", { size: 21, color: "1F2937", italics: true })]));

  c.push(h1("9. Mengapa Memilih Kami"));
  c.push(bullet("Produk sudah jadi dan teruji — implementasi cepat, bukan mulai dari nol."));
  c.push(bullet("Dibangun dengan teknologi standar industri yang mudah dirawat dan dikembangkan."));
  c.push(bullet("Fleksibel — tersedia beberapa skema kerjasama menyesuaikan kebutuhan dan anggaran Anda."));
  c.push(bullet("Dukungan dan pemeliharaan berkelanjutan setelah serah terima."));

  c.push(h1("10. Langkah Selanjutnya"));
  c.push(num("Jadwalkan sesi demo produk (online atau langsung)."));
  c.push(num("Pilih skema kerjasama dan paket yang sesuai (lihat dokumen “Skema Kerjasama & Investasi”)."));
  c.push(num("Penandatanganan kesepakatan dan pembayaran uang muka."));
  c.push(num("Implementasi, penyesuaian data awal, dan pelatihan pengguna."));
  c.push(num("Serah terima (go-live) dan mulai masa dukungan."));

  c.push(rule());
  c.push(h2("Kontak"));
  c.push(p([t("[Nama Anda / Nama Vendor]", { bold: true, size: 22, color: SLATE })]));
  c.push(p([t("WhatsApp / Telepon: ", { size: 21, color: "1F2937" }), t("[No. HP]", { size: 21, color: BLUE })]));
  c.push(p([t("Email: ", { size: 21, color: "1F2937" }), t("[alamat email]", { size: 21, color: BLUE })]));
  c.push(p([t("Alamat / Website: ", { size: 21, color: "1F2937" }), t("[opsional]", { size: 21, color: SLATE_LIGHT })]));

  return baseDoc(c, "Proposal Penawaran Produk SIMAC");
}

/* =====================================================================
   DOCUMENT 2 — SKEMA KERJASAMA & INVESTASI
   ===================================================================== */
function buildScheme() {
  const c = [];
  c.push(...coverBlock(
    "Skema Kerjasama", "SIMAC", "Sistem Manajemen Layanan Maintenance AC",
    "SKEMA KERJASAMA & INVESTASI",
    [
      ["Disiapkan untuk", "[Nama Calon Klien / Perusahaan]"],
      ["Disiapkan oleh", "[Nama Anda / Nama Vendor]"],
      ["Kontak", "[No. HP / WhatsApp] • [Email]"],
      ["Tanggal", "24 Agustus 2026"],
      ["Masa Berlaku Penawaran", "30 hari sejak tanggal dokumen"],
    ]
  ));

  c.push(callout("Cara Membaca Dokumen Ini", [
    "Seluruh angka harga di dokumen ini adalah CONTOH — silakan sesuaikan dengan strategi harga Anda.",
    "Ganti semua teks di dalam tanda kurung siku [ ... ] dengan data Anda yang sebenarnya.",
    "Tiga model kerjasama ditawarkan agar dapat menyesuaikan kebutuhan & anggaran calon klien.",
  ]));

  c.push(h1("1. Pengantar"));
  c.push(p("Dokumen ini menjelaskan pilihan model kerjasama, paket, dan rincian investasi untuk pengadaan aplikasi SIMAC. Tujuannya adalah memberikan opsi yang transparan sehingga Anda dapat memilih skema yang paling sesuai dengan kebutuhan operasional dan anggaran bisnis Anda."));

  c.push(h1("2. Pilihan Model Kerjasama"));
  c.push(h2("Model A — Beli Putus (Lisensi Sekali Bayar)"));
  c.push(p("Anda membayar satu kali dan memperoleh hak pakai penuh atas aplikasi SIMAC yang dipasang di server/hosting milik Anda sendiri. Cocok bagi bisnis yang ingin kepemilikan penuh dan biaya operasional bulanan minimal."));
  c.push(bullet("Aplikasi dipasang di server/hosting milik klien."));
  c.push(bullet("Termasuk instalasi, data awal, dan pelatihan."));
  c.push(bullet("Opsi penyerahan source code (biaya terpisah, lihat add-on)."));
  c.push(bullet("Biaya pemeliharaan setelah masa garansi bersifat opsional (lihat bagian Dukungan)."));

  c.push(h2("Model B — Berlangganan (SaaS Bulanan/Tahunan)"));
  c.push(p("Anda membayar biaya berlangganan berkala; aplikasi, hosting, pembaruan, dan dukungan dasar kami kelola sepenuhnya. Cocok bagi bisnis yang ingin biaya awal ringan dan tanpa repot mengurus server."));
  c.push(bullet("Hosting, backup, pembaruan, dan dukungan sudah termasuk."));
  c.push(bullet("Biaya awal (setup) jauh lebih ringan dibanding beli putus."));
  c.push(bullet("Dapat berhenti berlangganan sesuai ketentuan kontrak."));

  c.push(h2("Model C — White-Label / Kustom (Enterprise)"));
  c.push(p("Aplikasi disesuaikan dengan merek dan kebutuhan khusus Anda (logo, warna, fitur tambahan), atau kerjasama bagi hasil untuk skala usaha yang lebih besar. Cocok bagi bisnis yang ingin identitas sendiri atau kebutuhan di luar fitur standar."));
  c.push(bullet("Penyesuaian merek (logo, nama, warna) dan domain sendiri."));
  c.push(bullet("Pengembangan fitur khusus sesuai kebutuhan."));
  c.push(bullet("Skema harga/bagi hasil disepakati secara khusus."));

  c.push(new Paragraph({ children: [new PageBreak()] }));
  c.push(h1("3. Perbandingan Paket"));
  c.push(makeTable(
    ["Yang Termasuk", "A — Beli Putus", "B — Berlangganan", "C — White-Label"],
    [
      ["Aplikasi SIMAC (fitur standar)", "✓", "✓", "✓"],
      ["Instalasi & konfigurasi", "✓", "✓", "✓"],
      ["Migrasi data awal", "✓", "✓", "✓"],
      ["Pelatihan pengguna", "✓", "✓", "✓"],
      ["Hosting dikelola vendor", "–", "✓", "Opsional"],
      ["Pembaruan & backup rutin", "Opsional", "✓", "✓"],
      ["Penyesuaian merek (branding)", "–", "–", "✓"],
      ["Fitur kustom", "Add-on", "Add-on", "✓"],
      ["Penyerahan source code", "Add-on", "–", "Sesuai kesepakatan"],
    ],
    [4.4, 2, 2, 2.2],
    { headAlign: [AlignmentType.LEFT, AlignmentType.CENTER, AlignmentType.CENTER, AlignmentType.CENTER],
      align: [AlignmentType.LEFT, AlignmentType.CENTER, AlignmentType.CENTER, AlignmentType.CENTER] }
  ));

  c.push(h1("4. Rincian Investasi (Contoh)"));
  c.push(p([t("Angka di bawah adalah ", { size: 21, color: "1F2937" }), t("ilustrasi", { bold: true, size: 21, color: BLUE }), t(" — sesuaikan dengan penetapan harga Anda.", { size: 21, color: "1F2937" })]));
  c.push(h2("Model A — Beli Putus"));
  c.push(makeTable(
    ["Komponen", "Biaya (contoh)", "Keterangan"],
    [
      ["Lisensi + instalasi + pelatihan", "Rp [15.000.000]", "Sekali bayar"],
      ["Pemeliharaan tahunan (opsional)", "Rp [2.400.000] / tahun", "Update, perbaikan bug, dukungan"],
      ["Penyerahan source code (opsional)", "Rp [5.000.000]", "Sekali bayar"],
    ],
    [4.5, 3, 4],
    { align: [AlignmentType.LEFT, AlignmentType.RIGHT, AlignmentType.LEFT], boldCol: [1] }
  ));
  c.push(spacer(80));
  c.push(h2("Model B — Berlangganan"));
  c.push(makeTable(
    ["Paket", "Biaya Setup (contoh)", "Biaya Langganan (contoh)"],
    [
      ["Bulanan", "Rp [1.500.000]", "Rp [500.000] / bulan"],
      ["Tahunan (hemat)", "Rp [1.500.000]", "Rp [5.000.000] / tahun"],
    ],
    [4, 3, 3],
    { align: [AlignmentType.LEFT, AlignmentType.RIGHT, AlignmentType.RIGHT], boldCol: [2] }
  ));
  c.push(spacer(80));
  c.push(h2("Model C — White-Label / Kustom"));
  c.push(p([t("Harga disusun berdasarkan lingkup penyesuaian dan fitur tambahan yang disepakati. ", { size: 21, color: "1F2937" }), t("Mulai dari Rp [25.000.000]", { bold: true, size: 21, color: BLUE }), t(" atau skema bagi hasil sesuai kesepakatan.", { size: 21, color: "1F2937" })]));

  c.push(h1("5. Lingkup Pekerjaan yang Termasuk"));
  c.push(bullet("Instalasi aplikasi SIMAC pada server/hosting yang ditentukan."));
  c.push(bullet("Konfigurasi awal (identitas usaha, daftar layanan, akun pengguna)."));
  c.push(bullet("Migrasi data awal pelanggan & unit AC (format data disepakati)."));
  c.push(bullet("Pelatihan pengguna untuk peran Admin, Owner, dan Teknisi."));
  c.push(bullet("Masa garansi perbaikan bug setelah go-live (lihat bagian Garansi)."));
  c.push(spacer(60));
  c.push(p([t("Di luar lingkup: ", { bold: true, size: 21, color: SLATE }), t("pengadaan server/hosting & domain, biaya pihak ketiga, serta fitur baru yang tidak tercantum dalam penawaran ini (dapat diajukan sebagai add-on).", { size: 21, color: "1F2937" })]));

  c.push(new Paragraph({ children: [new PageBreak()] }));
  c.push(h1("6. Layanan Dukungan & Pemeliharaan"));
  c.push(makeTable(
    ["Tingkat Layanan", "Cakupan", "Waktu Respons (contoh)"],
    [
      ["Dasar", "Perbaikan bug & pertanyaan penggunaan", "1–2 hari kerja"],
      ["Standar", "Dasar + pembaruan minor & backup rutin", "1 hari kerja"],
      ["Prioritas", "Standar + dukungan prioritas & telepon", "Beberapa jam kerja"],
    ],
    [3, 5, 3]
  ));
  c.push(spacer(80));
  c.push(p("Jam dukungan: [Senin–Jumat, 09.00–17.00 WIB], di luar hari libur nasional. Tingkat layanan dan waktu respons dapat disesuaikan sesuai kesepakatan."));

  c.push(h1("7. Add-on & Kustomisasi"));
  c.push(makeTable(
    ["Layanan Tambahan", "Biaya (contoh)"],
    [
      ["Penyesuaian merek (logo, warna, nama)", "Rp [3.000.000]"],
      ["Integrasi payment gateway online", "Rp [7.000.000] +"],
      ["Notifikasi WhatsApp otomatis (API)", "Rp [5.000.000] + biaya layanan"],
      ["Laporan / fitur khusus", "Sesuai lingkup"],
      ["Pelatihan tambahan (per sesi)", "Rp [750.000]"],
    ],
    [6, 4],
    { align: [AlignmentType.LEFT, AlignmentType.RIGHT], boldCol: [1] }
  ));

  c.push(h1("8. Termin & Metode Pembayaran"));
  c.push(num("Uang muka (DP) 50% saat penandatanganan kesepakatan."));
  c.push(num("Pelunasan 50% saat serah terima (go-live)."));
  c.push(num("Untuk model berlangganan: pembayaran di muka setiap periode (bulanan/tahunan)."));
  c.push(spacer(60));
  c.push(p([t("Pembayaran melalui transfer ke: ", { size: 21, color: "1F2937" }), t("[Nama Bank] – [No. Rekening] a.n. [Nama Penerima]", { size: 21, color: SLATE, bold: true })]));

  c.push(h1("9. Estimasi Waktu Implementasi"));
  c.push(makeTable(
    ["Tahap", "Estimasi (contoh)"],
    [
      ["Persiapan & konfigurasi awal", "1–2 hari kerja"],
      ["Migrasi data awal", "1–3 hari kerja"],
      ["Pelatihan pengguna", "1 hari kerja"],
      ["Go-live & serah terima", "—"],
    ],
    [6, 4],
    { align: [AlignmentType.LEFT, AlignmentType.RIGHT] }
  ));
  c.push(spacer(60));
  c.push(p("Total estimasi umumnya [3–7 hari kerja] tergantung volume data dan kesiapan klien. Kustomisasi menambah waktu sesuai lingkup."));

  c.push(h1("10. Garansi"));
  c.push(p("Kami memberikan garansi perbaikan bug selama [30–90 hari] setelah go-live tanpa biaya tambahan, untuk cacat pada fitur standar yang telah disepakati. Garansi tidak mencakup permintaan fitur baru, kesalahan penggunaan, kerusakan akibat perubahan oleh pihak lain, atau masalah pada server/hosting di luar kendali kami."));

  c.push(h1("11. Syarat & Ketentuan"));
  c.push(bullet("Penawaran ini berlaku selama [30 hari] sejak tanggal dokumen."));
  c.push(bullet("Harga belum termasuk pajak (PPN) kecuali dinyatakan lain."));
  c.push(bullet("Hak cipta atas kode aplikasi tetap pada pengembang, kecuali disepakati penyerahan source code."));
  c.push(bullet("Klien menyediakan data awal dalam format yang disepakati."));
  c.push(bullet("Perubahan lingkup setelah kesepakatan dapat memengaruhi biaya dan jadwal."));
  c.push(bullet("Ketentuan lengkap dituangkan dalam surat perjanjian/kontrak terpisah."));

  c.push(h1("12. Persetujuan"));
  c.push(p("Dengan menandatangani di bawah ini, kedua pihak menyatakan setuju atas skema, paket, dan ketentuan yang dipilih untuk kemudian dituangkan dalam perjanjian resmi."));
  c.push(spacer(160));
  c.push(signatureRow("Pihak Vendor / Pengembang", "Pihak Klien"));

  return baseDoc(c, "Skema Kerjasama & Investasi SIMAC");
}

// Shared signature row (two columns)
function signatureRow(leftLabel, rightLabel) {
  const sigW = Math.floor(CONTENT_W / 2) - 200;
  const sigCell = (label) => new TableCell({
    width: { size: sigW, type: WidthType.DXA },
    margins: { top: 80, bottom: 80, left: 120, right: 120 },
    borders: noBorders,
    children: [
      new Paragraph({ spacing: { after: 60 }, children: [new TextRun({ text: label, font: FONT, bold: true, color: SLATE, size: 20 })] }),
      spacer(360),
      new Paragraph({ border: { top: { style: BorderStyle.SINGLE, size: 6, color: SLATE } }, spacing: { after: 40 }, children: [t("")] }),
      new Paragraph({ children: [new TextRun({ text: "Nama & Tanda Tangan", font: FONT, color: SLATE_LIGHT, size: 18 })] }),
      new Paragraph({ children: [new TextRun({ text: "Tanggal: ____________________", font: FONT, color: SLATE_LIGHT, size: 18 })] }),
    ],
  });
  return new Table({
    columnWidths: [sigW, 400, sigW],
    width: { size: CONTENT_W, type: WidthType.DXA },
    borders: noBorders,
    rows: [ new TableRow({ children: [ sigCell(leftLabel), new TableCell({ width: { size: 400, type: WidthType.DXA }, borders: noBorders, children: [spacer(0)] }), sigCell(rightLabel) ] }) ],
  });
}

/* =====================================================================
   DOCUMENT 3 — SURAT PERJANJIAN KERJASAMA (KONTRAK)
   ===================================================================== */
function clauseTitle(no, text) {
  return new Paragraph({
    spacing: { before: 260, after: 100 },
    alignment: AlignmentType.CENTER,
    children: [
      new TextRun({ text: `PASAL ${no}`, font: FONT, bold: true, color: BLUE_DARK, size: 22 }),
      new TextRun({ text: "\n", font: FONT }),
    ],
  });
}
function clauseHeading(no, title) {
  return [
    new Paragraph({ spacing: { before: 280, after: 20 }, alignment: AlignmentType.CENTER,
      children: [new TextRun({ text: `PASAL ${no}`, font: FONT, bold: true, color: BLUE_DARK, size: 22 })] }),
    new Paragraph({ spacing: { after: 120 }, alignment: AlignmentType.CENTER,
      children: [new TextRun({ text: title.toUpperCase(), font: FONT, bold: true, color: BLUE, size: 20 })] }),
  ];
}

function buildContract() {
  const c = [];
  // Title
  c.push(spacer(200));
  c.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 40 },
    children: [new TextRun({ text: "SURAT PERJANJIAN KERJASAMA", font: FONT, bold: true, color: BLUE_DARK, size: 32 })] }));
  c.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 40 },
    children: [new TextRun({ text: "PENGADAAN & IMPLEMENTASI APLIKASI SIMAC", font: FONT, bold: true, color: BLUE, size: 24 })] }));
  c.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 60 },
    children: [new TextRun({ text: "Sistem Manajemen Layanan Maintenance AC", font: FONT, italics: true, color: SLATE, size: 20 })] }));
  c.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 120 },
    children: [new TextRun({ text: "Nomor: [___/SPK/____/2026]", font: FONT, color: SLATE, size: 20 })] }));
  c.push(rule());

  c.push(p("Perjanjian Kerjasama ini (“Perjanjian”) dibuat dan ditandatangani pada hari [__________] tanggal [__] bulan [__________] tahun 2026, oleh dan antara:"));

  // Parties
  c.push(makeTable(
    ["", "Keterangan"],
    [
      ["Nama", "[Nama Anda / Nama Vendor]"],
      ["Jabatan", "[Pemilik / Direktur]"],
      ["Alamat", "[Alamat Vendor]"],
      ["Kontak", "[No. HP] • [Email]"],
    ],
    [2.2, 7.8]
  ));
  c.push(p([t("Dalam hal ini bertindak untuk dan atas nama diri sendiri/perusahaan, selanjutnya disebut sebagai ", { size: 21, color: "1F2937" }), t("“PIHAK PERTAMA” (Pengembang)", { bold: true, size: 21, color: SLATE }), t(".", { size: 21, color: "1F2937" })]));
  c.push(spacer(60));
  c.push(makeTable(
    ["", "Keterangan"],
    [
      ["Nama", "[Nama Perwakilan Klien]"],
      ["Jabatan", "[Jabatan]"],
      ["Perusahaan", "[Nama Perusahaan Klien]"],
      ["Alamat", "[Alamat Klien]"],
      ["Kontak", "[No. HP] • [Email]"],
    ],
    [2.2, 7.8]
  ));
  c.push(p([t("Dalam hal ini bertindak untuk dan atas nama [Nama Perusahaan Klien], selanjutnya disebut sebagai ", { size: 21, color: "1F2937" }), t("“PIHAK KEDUA” (Klien)", { bold: true, size: 21, color: SLATE }), t(".", { size: 21, color: "1F2937" })]));
  c.push(spacer(80));
  c.push(p("PIHAK PERTAMA dan PIHAK KEDUA secara bersama-sama disebut “Para Pihak”. Para Pihak sepakat mengikatkan diri dalam Perjanjian ini dengan ketentuan sebagai berikut:"));

  // Pasal 1 — Definisi & Ruang Lingkup
  c.push(...clauseHeading(1, "Ruang Lingkup Pekerjaan"));
  c.push(p("PIHAK PERTAMA menyediakan aplikasi SIMAC beserta jasa implementasi kepada PIHAK KEDUA, dengan lingkup pekerjaan meliputi:"));
  c.push(num("Penyediaan aplikasi SIMAC dengan fitur standar sesuai dokumen penawaran.", "clauses"));
  c.push(num("Instalasi dan konfigurasi awal pada server/hosting yang ditentukan.", "clauses"));
  c.push(num("Migrasi data awal (pelanggan dan unit AC) dalam format yang disepakati.", "clauses"));
  c.push(num("Pelatihan pengguna untuk peran Admin, Owner, dan Teknisi.", "clauses"));
  c.push(num("Model kerjasama yang dipilih: [Beli Putus / Berlangganan / White-Label] (coret yang tidak perlu).", "clauses"));

  // Pasal 2 — Nilai & Cara Pembayaran
  c.push(...clauseHeading(2, "Nilai Perjanjian & Cara Pembayaran"));
  c.push(p([t("Nilai total pekerjaan adalah sebesar ", { size: 21, color: "1F2937" }), t("Rp [__________] ([terbilang])", { bold: true, size: 21, color: SLATE }), t(", dengan skema pembayaran:", { size: 21, color: "1F2937" })]));
  c.push(num("Uang muka (DP) sebesar 50% dibayarkan saat penandatanganan Perjanjian ini.", "clauses"));
  c.push(num("Pelunasan sebesar 50% dibayarkan saat serah terima (go-live).", "clauses"));
  c.push(num("Untuk model berlangganan, biaya langganan sebesar Rp [__________] dibayar di muka tiap [bulan/tahun].", "clauses"));
  c.push(p([t("Pembayaran ditransfer ke rekening: ", { size: 21, color: "1F2937" }), t("[Nama Bank] – [No. Rekening] a.n. [Nama Penerima]", { bold: true, size: 21, color: SLATE }), t(".", { size: 21, color: "1F2937" })]));

  // Pasal 3 — Jangka Waktu
  c.push(...clauseHeading(3, "Jangka Waktu Pelaksanaan"));
  c.push(p("Pekerjaan dilaksanakan selama [__] hari kerja terhitung sejak diterimanya uang muka dan kelengkapan data dari PIHAK KEDUA. Keterlambatan penyediaan data atau akses oleh PIHAK KEDUA memperpanjang jangka waktu secara proporsional."));

  c.push(new Paragraph({ children: [new PageBreak()] }));

  // Pasal 4 — Hak & Kewajiban
  c.push(...clauseHeading(4, "Hak & Kewajiban Para Pihak"));
  c.push(p([t("Kewajiban PIHAK PERTAMA:", { bold: true, size: 21, color: SLATE })]));
  c.push(bullet("Menyelesaikan pekerjaan sesuai ruang lingkup dan jangka waktu."));
  c.push(bullet("Memberikan pelatihan dan dukungan sesuai ketentuan Perjanjian."));
  c.push(bullet("Menjaga kerahasiaan data PIHAK KEDUA."));
  c.push(spacer(40));
  c.push(p([t("Kewajiban PIHAK KEDUA:", { bold: true, size: 21, color: SLATE })]));
  c.push(bullet("Melakukan pembayaran tepat waktu sesuai Pasal 2."));
  c.push(bullet("Menyediakan data awal, akses server/hosting, dan informasi yang diperlukan."));
  c.push(bullet("Menunjuk penanggung jawab (PIC) selama implementasi."));

  // Pasal 5 — Hak Kekayaan Intelektual
  c.push(...clauseHeading(5, "Hak Kekayaan Intelektual"));
  c.push(p("Hak cipta dan seluruh hak kekayaan intelektual atas aplikasi SIMAC tetap menjadi milik PIHAK PERTAMA. PIHAK KEDUA memperoleh hak pakai (lisensi) sesuai model kerjasama yang dipilih. Penyerahan source code hanya berlaku apabila disepakati secara tertulis dan dibayarkan sesuai ketentuan add-on."));

  // Pasal 6 — Garansi & Pemeliharaan
  c.push(...clauseHeading(6, "Garansi & Pemeliharaan"));
  c.push(p("PIHAK PERTAMA memberikan garansi perbaikan bug selama [__] hari setelah go-live tanpa biaya tambahan untuk cacat pada fitur standar. Garansi tidak mencakup permintaan fitur baru, kesalahan penggunaan, perubahan oleh pihak lain, atau gangguan pada server/hosting. Pemeliharaan setelah masa garansi diatur dalam kesepakatan terpisah."));

  // Pasal 7 — Kerahasiaan
  c.push(...clauseHeading(7, "Kerahasiaan"));
  c.push(p("Para Pihak wajib menjaga kerahasiaan seluruh informasi dan data yang diperoleh selama pelaksanaan Perjanjian, dan tidak mengungkapkannya kepada pihak ketiga tanpa persetujuan tertulis, baik selama maupun setelah Perjanjian berakhir."));

  // Pasal 8 — Force Majeure
  c.push(...clauseHeading(8, "Keadaan Kahar (Force Majeure)"));
  c.push(p("Para Pihak dibebaskan dari tanggung jawab atas keterlambatan atau kegagalan pelaksanaan kewajiban yang disebabkan oleh keadaan di luar kendali yang wajar, seperti bencana alam, kebakaran, huru-hara, kebijakan pemerintah, atau gangguan infrastruktur publik. Pihak yang terdampak wajib memberitahukan pihak lain dalam waktu [7] hari."));

  // Pasal 9 — Pengakhiran
  c.push(...clauseHeading(9, "Pengakhiran Perjanjian"));
  c.push(p("Perjanjian dapat diakhiri apabila salah satu pihak melanggar ketentuan dan tidak memperbaikinya dalam [14] hari setelah pemberitahuan tertulis. Pengakhiran tidak menghapus kewajiban pembayaran atas pekerjaan yang telah dilaksanakan."));

  // Pasal 10 — Penyelesaian Sengketa
  c.push(...clauseHeading(10, "Penyelesaian Sengketa & Hukum yang Berlaku"));
  c.push(p("Perjanjian ini tunduk pada hukum Republik Indonesia. Setiap perselisihan diselesaikan terlebih dahulu secara musyawarah. Apabila tidak tercapai kesepakatan, Para Pihak sepakat menyelesaikannya melalui [Pengadilan Negeri __________ / arbitrase]."));

  // Pasal 11 — Penutup
  c.push(...clauseHeading(11, "Penutup"));
  c.push(p("Perjanjian ini dibuat dalam 2 (dua) rangkap bermeterai cukup, masing-masing memiliki kekuatan hukum yang sama, dan berlaku sejak tanggal ditandatangani oleh Para Pihak dalam keadaan sadar dan tanpa paksaan."));

  c.push(spacer(200));
  c.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 40 },
    children: [new TextRun({ text: "Ditandatangani di [Kota], pada tanggal tersebut di atas.", font: FONT, size: 20, color: SLATE })] }));
  c.push(spacer(160));
  c.push(signatureRow("PIHAK PERTAMA (Pengembang)", "PIHAK KEDUA (Klien)"));
  c.push(spacer(120));
  c.push(new Paragraph({ alignment: AlignmentType.CENTER,
    children: [new TextRun({ text: "Materai Rp10.000 ditempelkan pada kolom tanda tangan masing-masing pihak.", font: FONT, italics: true, color: SLATE_LIGHT, size: 16 })] }));

  return baseDoc(c, "Surat Perjanjian Kerjasama SIMAC");
}

// ---- Write files ----
(async () => {
  const outDir = __dirname;
  fs.writeFileSync(`${outDir}/01-Proposal-Penawaran-SIMAC.docx`, await Packer.toBuffer(buildProposal()));
  fs.writeFileSync(`${outDir}/02-Skema-Kerjasama-Investasi-SIMAC.docx`, await Packer.toBuffer(buildScheme()));
  fs.writeFileSync(`${outDir}/03-Surat-Perjanjian-Kerjasama-SIMAC.docx`, await Packer.toBuffer(buildContract()));
  console.log("Generated 3 documents.");
})();
