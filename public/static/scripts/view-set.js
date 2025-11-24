document.getElementById('save-all').addEventListener('click', async () => {
  const items = Array.from(document.querySelectorAll('.question-item'));
  const updates = items.map(item => {
    const id = parseInt(item.dataset.id, 10);
    const type = item.dataset.type;
    const prompt = item.querySelector('.q-prompt')?.value?.trim() || '';

    const upd = { id, type, prompt };

    if (type === 'Multiple Choice') {
      const opts = Array.from(item.querySelectorAll('.mc-opt')).map(i => i.value);
      const checked = item.querySelector('input[name="correctIndex-' + id + '"]:checked');
      upd.options = opts;
      upd.correctIndex = checked ? parseInt(checked.value, 10) : null;
    } else if (type === 'True or False') {
      const checked = item.querySelector('input[name="correctBool-' + id + '"]:checked');
      upd.correctBool = checked ? (checked.value === '1') : null;
    } else if (type === 'Response') {
      upd.correctText = item.querySelector('.resp-text')?.value || '';
    }

    return upd;
  });

  try {
    const res = await fetch('api/questions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ updates })
    });

    if (!res.ok) {
      const t = await res.text();
      throw new Error(`HTTP ${res.status}: ${t}`);
    }

    const ct = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
      const t = await res.text();
      throw new Error(`Expected JSON, got: ${t.substring(0, 200)}…`);
    }

    const data = await res.json();
    if (!data.ok) {
      console.error('Bulk update error:', data);
      alert('Save failed: ' + (data.error || 'Unknown'));
      return;
    }

    // Success: go back to sets
    window.location.href = 'index.php?command=sets';
  } catch (err) {
    console.error(err);
    alert('Save failed. See console for details.');
  }
});