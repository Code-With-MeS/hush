
document.getElementById("noteBtn").addEventListener("click", () => {
  document.getElementById("note").classList.toggle("hidden");
});
async function loadReviews(){
  try{
    const res = await fetch("review.php");
    const data = await res.json();
    renderReviews(Array.isArray(data) ? data : []);
  }catch(e){
    console.error(e);
  }
}
function renderReviews(list){
  const wrap = document.getElementById("reviews");
  wrap.innerHTML = "";
  if(!list.length){
    wrap.innerHTML = `<p style="opacity:.7">No messages yet. Be the first 💌</p>`;
    return;
  }
  list.slice(0,20).forEach(r=>{
    const div = document.createElement("div");
    div.className = "review";
    const name = r.name?.trim() ? r.name.trim() : "Someone";
    const when = new Date(r.ts).toLocaleString();
    div.innerHTML = `
      <div class="meta">${name} · ${when}</div>
      <div class="text">${escapeHTML(r.message || "")}</div>`;
    wrap.appendChild(div);
  });
}
function escapeHTML(s){
  return (s||"").replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

document.getElementById("reviewForm").addEventListener("submit", async (e)=>{
  e.preventDefault();
  const status = document.getElementById("status");
  status.textContent = "Sending…";
  const fd = new FormData(e.target);
  try{
    const res = await fetch("review.php", { method:"POST", body:fd });
    const out = await res.json();
    if(out.ok){
      e.target.reset();
      status.textContent = "Thanks! Your message was saved 💖";
      loadReviews();
    }else{
      status.textContent = out.error || "Something went wrong.";
    }
  }catch(err){
    status.textContent = "Network error. Please try again.";
  }
});
loadReviews();
