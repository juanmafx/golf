import { beforeEach, describe, expect, it } from 'vitest';
import { initNavigation } from '../src/modules/navigation';

describe('initNavigation', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <button data-nav-toggle aria-expanded="false">Menu</button>
      <nav data-nav><a href="/login">Log in</a></nav>`;
  });

  const toggle = (): HTMLButtonElement => document.querySelector<HTMLButtonElement>('[data-nav-toggle]')!;
  const navigation = (): HTMLElement => document.querySelector<HTMLElement>('[data-nav]')!;

  it('returns false when the markup is absent', () => {
    document.body.innerHTML = '';
    expect(initNavigation(document)).toBe(false);
  });

  it('opens and closes on click', () => {
    expect(initNavigation(document)).toBe(true);
    toggle().click();
    expect(toggle().getAttribute('aria-expanded')).toBe('true');
    expect(navigation().classList.contains('is-open')).toBe(true);
    toggle().click();
    expect(toggle().getAttribute('aria-expanded')).toBe('false');
    expect(navigation().classList.contains('is-open')).toBe(false);
  });

  it('closes on Escape and returns focus to the toggle', () => {
    initNavigation(document);
    toggle().click();
    navigation().querySelector('a')!.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
    expect(toggle().getAttribute('aria-expanded')).toBe('false');
    expect(document.activeElement).toBe(toggle());
  });
});
