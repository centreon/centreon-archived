import '@testing-library/jest-dom/extend-expect';

// eslint-disable-next-line @typescript-eslint/no-namespace
declare namespace jest {
  interface Matchers<R> {
    toBeInTheDocument(): R;
  }
}
