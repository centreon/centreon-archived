import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import { configure } from '@testing-library/dom';

const timeout = 10000;

configure({
  asyncUtilTimeout: timeout,
});

jest.setTimeout(timeout);

document.createRange = () => ({
  setStart: () => {},
  setEnd: () => {},
  commonAncestorContainer: {
    nodeName: 'BODY',
    ownerDocument: document,
  },
});

class IntersectionObserver {
  observe = jest.fn();

  unobserve = jest.fn();

  disconnect = jest.fn();

  current = this;
}

Object.defineProperty(window, 'IntersectionObserver', {
  writable: true,
  configurable: true,
  value: IntersectionObserver,
});

Object.defineProperty(global, 'IntersectionObserver', {
  writable: true,
  configurable: true,
  value: IntersectionObserver,
});

i18n.use(initReactI18next).init({
  nsSeparator: false,
  keySeparator: false,
  fallbackLng: 'en',
  lng: 'en',
  resources: {},
});
