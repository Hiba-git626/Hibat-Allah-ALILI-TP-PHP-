document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-delete-form]').forEach(form => form.addEventListener('submit', event => {
    if (!window.confirm('Voulez-vous vraiment supprimer cet étudiant ? Cette action est définitive.')) event.preventDefault();
  }));
  const search=document.querySelector('[data-live-search]'), rows=[...document.querySelectorAll('[data-student-row]')], empty=document.querySelector('.live-empty');
  if(search && rows.length && empty) search.addEventListener('input', () => {
    const q=search.value.trim().toLocaleLowerCase('fr'); let visible=0;
    rows.forEach(row => { const ok=row.dataset.search.includes(q); row.hidden=!ok; if(ok) visible++; });
    empty.hidden=visible!==0;
  });
});
