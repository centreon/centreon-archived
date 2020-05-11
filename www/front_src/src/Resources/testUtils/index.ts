import UserEvent from '@testing-library/user-event';
import { within } from '@testing-library/react';

export const getSelectPopover = (): HTMLElement =>
  document.body.querySelector('ul[role=listbox]') as HTMLElement;

export const selectOption = (element, optionText): void => {
  const selectButton = element.parentNode.querySelector('[role=button]');

  UserEvent.click(selectButton);

  const listItem = within(getSelectPopover()).getByText(optionText);
  UserEvent.click(listItem);
};
