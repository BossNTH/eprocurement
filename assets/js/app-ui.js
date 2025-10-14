// app-ui.js — tiny helpers (no dependencies)
document.addEventListener("click", (e) => {
  const t = e.target;
  // Modal toggles
  if (t.matches("[data-open]")) {
    const id = t.getAttribute("data-open");
    const m = document.getElementById(id);
    if (m) m.classList.add("open");
    document.querySelector(".modal-backdrop")?.classList.add("open");
  }
  if (t.matches("[data-close]") || t.classList.contains("modal-backdrop")) {
    document.querySelectorAll(".modal").forEach(m => m.classList.remove("open"));
    document.querySelector(".modal-backdrop")?.classList.remove("open");
  }
});
// Simple client-side table filter
function filterTable(inputId, tableId){
  const q = document.getElementById(inputId).value.toLowerCase();
  document.querySelectorAll(`#${tableId} tbody tr`).forEach(tr => {
    tr.style.display = tr.innerText.toLowerCase().includes(q) ? "" : "none";
  });
}
window.filterTable = filterTable;