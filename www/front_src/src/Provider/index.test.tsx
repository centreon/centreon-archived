import * as React from 'react';

import axios from 'axios';

import { render, RenderResult, waitFor } from '@testing-library/react';
import AppProvider from '.';
import { useUserContext as mockUseUserContext } from './UserContext';
import { UserContext } from './models';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const retrievedUser = {
  locale: 'en-EN',
  timezone: 'Europe/Paris',
  username: 'admin',
};

const retrievedActionsAcl = {
  host: {
    acknowledgement: true,
    check: true,
    downtime: true,
  },
  service: {
    acknowledgement: true,
    check: true,
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
