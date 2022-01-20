import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import dayjs from 'dayjs';
import timezonePlugin from 'dayjs/plugin/timezone';
import utcPlugin from 'dayjs/plugin/utc';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import { configure } from '@testing-library/dom';

const timeout = 10000;

configure({
  asyncUtilTimeout: timeout,
});

jest.setTimeout(timeout);

dayjs.extend(localizedFormat);
dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);

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
