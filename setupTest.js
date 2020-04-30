import { config } from 'react-transition-group';

// This prevents jsdom from maitaining the Select listbox open after clicking on an item
config.disabled = true;

document.createRange = () => ({
  setStart: () => {},
  setEnd: () => {},
  commonAncestorContainer: {
    nodeName: 'BODY',
    ownerDocument: document,
  },
});
