/* ============================================================
   PEERLEARN — SCRIPT.JS FINAL
   Render Tutor + Search + Animation
   ============================================================ */

// Data tutor dummy (bisa diganti dari backend)
const tutorsData = [
  { nama: "Rizky Ramadhan", mapel: "Matematika", harga: 350000, rating: 4.9 },
  { nama: "Aulia Putri", mapel: "Bahasa Inggris", harga: 420000, rating: 5.0 },
  { nama: "Dimas Wahyu", mapel: "Fisika", harga: 300000, rating: 4.7 },
  { nama: "Nadia Fitri", mapel: "Kimia", harga: 400000, rating: 4.8 },
  { nama: "Farhan Akbar", mapel: "Biologi", harga: 320000, rating: 4.6 },
  { nama: "Sinta Maharani", mapel: "Bahasa Indonesia", harga: 280000, rating: 4.5 },
  { nama: "Adi Pratama", mapel: "Ekonomi", harga: 330000, rating: 4.7 },
  { nama: "Maya Sari", mapel: "Sejarah", harga: 260000, rating: 4.4 },
];

// Format angka → Rp
function rupiah(n) {
  return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/* ============================================================
   1) RENDER TUTOR (Landing Page)
============================================================ */

function renderRekomendasi() {
  const container = document.getElementById("tutorContainer");
  if (!container) return;

  container.innerHTML = "";

  tutorsData.slice(0, 6).forEach(t => {
    const card = document.createElement("div");
    card.className = "tutor-card fade-up";

    card.innerHTML = `
      <div class="tutor-photo">${t.nama.charAt(0)}</div>
      <div class="tutor-name">${t.nama}</div>
      <div class="tutor-sub">${t.mapel}</div>

      <div class="tutor-meta">
        <div class="rating">★ ${t.rating}</div>
        <div class="tutor-price">Rp ${rupiah(t.harga)}</div>
      </div>

      <a 
        href="../public/detail_tutor.php?nama=${encodeURIComponent(t.nama)}&mapel=${encodeURIComponent(t.mapel)}&harga=${t.harga}&rating=${t.rating}"
        class="btn-detail"
      >
        Detail Tutor
      </a>
    `;

    container.appendChild(card);
  });
}

renderRekomendasi();


/* ============================================================
   2) FAQ Accordion
============================================================ */

document.querySelectorAll(".faq-item")?.forEach(item => {
  item.addEventListener("click", () => {
    const active = item.classList.contains("active");
    document.querySelectorAll(".faq-item").forEach(i => i.classList.remove("active"));
    if (!active) item.classList.add("active");
  });
});


/* ============================================================
   3) Scroll Reveal
============================================================ */

function reveal() {
  document.querySelectorAll(".fade-up").forEach(el => {
    const rect = el.getBoundingClientRect();
    if (rect.top < window.innerHeight - 70) {
      el.classList.add("show");
    }
  });
}

window.addEventListener("scroll", reveal);
window.addEventListener("load", reveal);
