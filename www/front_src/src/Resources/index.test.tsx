import React from 'react';

import axios from 'axios';
import { render, wait, within } from '@testing-library/react';
import UserEvent from '@testing-library/user-event';

import Resources from '.';
import {
  labelUnhandledProblems,
  labelResourceProblems,
  labelAll,
} from './translatedLabels';

const mockedAxios = axios as jest.Mocked<typeof axios>;

export const selectOption = (element, optionText): void => {
  const selectButton = element.parentNode.querySelector('[role=button]');

  UserEvent.click(selectButton);

  const listbox = document.body.querySelector(
    'ul[role=listbox]',
  ) as HTMLElement;

  const listItem = within(listbox).getByText(optionText);
  UserEvent.click(listItem);
};

describe(Resources, () => {
  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  beforeEach(() => {
    mockedAxios.get.mockResolvedValue({
      data: {
        result: [],
        meta: {
          page: 1,
          limit: 10,
          search: {},
          sort_by: {},
          total: 0,
        },
      },
    });
  });

  it('lists with unhnandled_problems state by default', async () => {
    render(<Resources />);

    await wait(() =>
      expect(
        mockedAxios.get,
      ).toHaveBeenCalledWith(
        'monitoring/resources?state=["unhandled_problems"]',
        { cancelToken: {} },
      ),
    );
  });

  it('executes a list request with selected state filter when state filter is changed', async () => {
    const { getByText } = render(<Resources />);

    await wait(() => expect(mockedAxios.get).toHaveBeenCalled());

    selectOption(getByText(labelUnhandledProblems), labelResourceProblems);

    await wait(() =>
      expect(
        mockedAxios.get,
      ).toHaveBeenCalledWith(
        'monitoring/resources?state=["resources_problems"]',
        { cancelToken: {} },
      ),
    );

    selectOption(getByText(labelResourceProblems), labelAll);

    await wait(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        'monitoring/resources?state=["all"]',
        {
          cancelToken: {},
        },
      ),
    );
  });
});
