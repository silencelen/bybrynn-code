document.addEventListener('DOMContentLoaded', () => {
  setupContentWrapper();
  attachLinkHandlers();
  window.addEventListener('popstate', () => loadPage(location.href, true));
});

function setupContentWrapper() {
  const header = document.querySelector('header');
  const footer = document.querySelector('footer');
  if (!header || !footer) return;
  let content = document.getElementById('content');
  if (!content) {
    content = document.createElement('div');
    content.id = 'content';
    let node = header.nextSibling;
    while (node && node !== footer) {
      const next = node.nextSibling;
      content.appendChild(node);
      node = next;
    }
    footer.parentNode.insertBefore(content, footer);
  }
}

function attachLinkHandlers() {
  document.querySelectorAll('a[href]').forEach(link => {
    if (isInternal(link)) {
      link.addEventListener('click', handleLink);
    }
  });
}

function handleLink(e) {
  if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
  e.preventDefault();
  const url = e.currentTarget.href;
  loadPage(url);
}

function isInternal(link) {
  const url = new URL(link.href, location.href);
  return url.origin === location.origin && !link.hash && !link.target;
}

async function loadPage(url, isPop) {
  const content = document.getElementById('content');
  if (!content) { location.href = url; return; }
  content.classList.add('fade-out');
  try {
    const response = await fetch(url);
    const text = await response.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(text, 'text/html');
    const header = doc.querySelector('header');
    const footer = doc.querySelector('footer');
    let newFragment = document.createDocumentFragment();
    let node = header.nextSibling;
    while (node && node !== footer) {
      newFragment.appendChild(node.cloneNode(true));
      node = node.nextSibling;
    }
    setTimeout(() => {
      content.innerHTML = '';
      content.appendChild(newFragment);
      executeScripts(content);
      document.title = doc.title;
      if (!isPop) history.pushState(null, '', url);
      attachLinkHandlers();
      content.classList.remove('fade-out');
      content.classList.add('fade-in');
      setTimeout(() => content.classList.remove('fade-in'), 300);
    }, 300);
  } catch (err) {
    console.error(err);
    location.href = url;
  }
}

function executeScripts(container) {
  container.querySelectorAll('script').forEach(oldScript => {
    const script = document.createElement('script');
    [...oldScript.attributes].forEach(attr => script.setAttribute(attr.name, attr.value));
    script.textContent = oldScript.textContent;
    oldScript.replaceWith(script);
  });
}
