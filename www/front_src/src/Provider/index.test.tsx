import * as React from 'react';

import axios from 'axios';

import { render, RenderResult, waitFor } from '@testing-library/react';
import AppProvider from '.';
import { useUserContext as mockUseUserContext } from './UserContext';
import { UserContext } from './models';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const retrievedUser = {
  username: 'admin',
  timezone: 'Europe/Paris',
  locale: 'en-EN',
};

const retrievedActionsAcl = {
  host: {
    check: true,
    acknowledgement: true,
    downtime: true,
  },
  service: {
    check: true,
    acknowledgement: true,
    downtime: true,
  },
};

const retrievedTranslations = {
  en: {
    hello: 'Hello',
  },
};

let userContext: UserContext | null = null;

jest.mock('../App', () => {
  const ComponentWithUserContext = (): JSX.Element => {
    userContext = mockUseUserContext();

    return <></>;
  };

  return {
    __esModule: true,
    default: ComponentWithUserContext,
  };
});

const renderComponent = (): RenderResult => {
  return render(<AppProvider />);
};

describe(AppProvider, () => {
  beforeEach(() => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: retrievedUser,
      })
      .mockResolvedValueOnce({
        data: retrievedTranslations,
      })
      .mockResolvedValueOnce({
        data: retrievedActionsAcl,
      });
  });

  afterEach(() => {
    userContext = null;
  });

  it('populates the userContext', async () => {
    renderComponent();

    await waitFor(() =>
      expect(userContext).toEqual({
        ...retrievedUser,
        acl: {
          actions: retrievedActionsAcl,
        },
      }),
    );
  });
});
