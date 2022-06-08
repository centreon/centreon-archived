import { createRoot } from 'react-dom/client';

import Main from './Main';

const container = document.getElementById('root') as HTMLElement;

createRoot(container).render(<Main />);
