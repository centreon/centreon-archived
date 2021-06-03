import 'dayjs/locale/en';

import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import dayjs from 'dayjs';
import timezonePlugin from 'dayjs/plugin/timezone';
import utcPlugin from 'dayjs/plugin/utc';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import ResizeObserver from 'resize-observer-polyfill';

window.ResizeObserver = ResizeObserver;

jest.setTimeout(10000);

dayjs.extend(localizedFormat);
dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);

document.createRange = () => ({
  commonAncestorContainer: {
    nodeName: 'BODY',
    ownerDocument: document,
  },
  setEnd: () => {},
  setStart: () => {},
});

class IntersectionObserver {
  observe = jest.fn();

  unobserve = jest.fn();

  disconnect = jest.fn();

  current = this;
}

Object.defineProperty(window, 'IntersectionObserver', {
  configurable: true,
  value: IntersectionObserver,
  writable: true,
});

Object.defineProperty(global, 'IntersectionObserver', {
  configurable: true,
  value: IntersectionObserver,
  writable: true,
});

i18n.use(initReactI18next).init({
  fallbackLng: 'en',
  keySeparator: false,
  lng: 'en',
  nsSeparator: false,
  resources: {},
});
