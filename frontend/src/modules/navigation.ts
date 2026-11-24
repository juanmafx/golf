export function initNavigation(root: ParentNode): boolean {
  const toggle = root.querySelector<HTMLButtonElement>('[data-nav-toggle]');
  const navigation = root.querySelector<HTMLElement>('[data-nav]');

  if (!toggle || !navigation) {
    return false;
  }

  const setOpen = (isOpen: boolean): void => {
    toggle.setAttribute('aria-expanded', String(isOpen));
    navigation.classList.toggle('is-open', isOpen);
  };

  toggle.addEventListener('click', () => {
    setOpen(toggle.getAttribute('aria-expanded') !== 'true');
  });

  navigation.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setOpen(false);
      toggle.focus();
    }
  });

  return true;
}
