import { createRoot } from 'react-dom/client';

import Main from './Main';

const container = document.getElementById('root') as HTMLElement;

const createApp = async (): Promise<void> => {
  window.React = await import(/* webpackChunkName: "external" */ 'react');

  createRoot(container).render(<Main />);
};

createApp();
