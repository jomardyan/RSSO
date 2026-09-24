(() => {
  const normalize = value => value.toLocaleLowerCase('pl').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/ł/g, 'l').trim();
  const search = document.getElementById('article-search');
  const cards = [...document.querySelectorAll('#article-grid .article-card')];
  const chips = [...document.querySelectorAll('.chip[data-filter]')];
  if (search && cards.length) {
    let selected = document.querySelector('.chip.active')?.dataset.filter || '';
    const update = () => {
      const query = normalize(search.value);
      let count = 0;
      cards.forEach(card => {
        const visible = (!selected || card.dataset.topic === selected) && normalize(card.dataset.search || '').includes(query);
        card.hidden = !visible;
        if (visible) count++;
      });
      document.getElementById('results-count').textContent = `Pokazano ${count} ${count === 1 ? 'przewodnik' : count >= 2 && count <= 4 ? 'przewodniki' : 'przewodników'}`;
      document.getElementById('no-results').hidden = count !== 0;
    };
    search.addEventListener('input', update);
    chips.forEach(chip => chip.addEventListener('click', () => {
      selected = chip.dataset.filter;
      chips.forEach(item => item.classList.toggle('active', item === chip));
      const url = new URL(location.href);
      if (selected) url.searchParams.set('topic', selected); else url.searchParams.delete('topic');
      history.replaceState(null, '', url);
      update();
    }));
    update();
  }
  const termSearch = document.getElementById('term-search');
  if (termSearch) {
    const terms = [...document.querySelectorAll('.term[data-search]')];
    termSearch.addEventListener('input', () => {
      const query = normalize(termSearch.value);
      let count = 0;
      terms.forEach(term => {
        term.hidden = !normalize(term.dataset.search || '').includes(query);
        if (!term.hidden) count++;
      });
      document.getElementById('term-empty').hidden = count !== 0;
    });
  }
})();
