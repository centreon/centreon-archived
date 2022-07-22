import axios from 'axios';
import { BrowserRouter } from 'react-router-dom';
import { Provider } from 'jotai';
import mockdate from 'mockdate';
import userEvent from '@testing-library/user-event';

import { render, RenderResult, waitFor, screen } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { retrievedNavigation } from '../../Navigation/mocks';
import { areUserParametersLoadedAtom } from '../../Main/useUser';
import { logoutEndpoint } from '../../api/endpoint';

import { userEndpoint } from './api/endpoint';
import { labelProfile } from './translatedLabels';

import UserMenu from '.';

window.document.execCommand = jest.fn();

jest.mock('@centreon/ui-context', () =>
  jest.requireActual('centreon-frontend/packages/ui-context'),
);

const mockedAxios = axios as jest.Mocked<typeof axios>;

const retrievedUser = {
  alias: 'Admin alias',
  default_page: '/monitoring/resources',
  is_export_button_enabled: true,
  locale: 'en_US.UTF8',
  name: 'Admin',
  timezone: 'Europe/Paris',
  use_deprecated_pages: false,
};

mockdate.set('2022-01-01T12:20:00Z');

const renderUserMenu = (): RenderResult =>
  render(
    <Provider
      initialValues={[
        [userAtom, retrievedUser],
        [areUserParametersLoadedAtom, true],
      ]}
    >
      <BrowserRouter>
        <UserMenu />
      </BrowserRouter>
    </Provider>,
  );

const retrievedUserData = {
  fullname: 'Admin admin',
  username: 'admin',
};

const retrievedUserDataAutologinKey = {
  autologinkey: 'autologinKey',
  fullname: 'Admin admin',
  username: 'admin',
};

const cancelTokenRequestParam = { cancelToken: {} };

const labelLogout = 'Logout';
const labelCopyAutologinLink = 'Copy autologin link';

const mockRequests = (): void => {
  mockedAxios.get.mockReset();
  mockedAxios.get
    .mockResolvedValue({ data: retrievedNavigation })
    .mockResolvedValue({ data: retrievedUserData });
};

const mockRequestsWithAutologinKey = (): void => {
  mockedAxios.get.mockReset();
  mockedAxios.get
    .mockResolvedValue({ data: retrievedNavigation })
    .mockResolvedValue({ data: retrievedUserDataAutologinKey });
};

const mockRequestsWithLogout = (): void => {
  mockedAxios.get.mockReset();
  mockedAxios.get
    .mockResolvedValue({ data: retrievedNavigation })
    .mockResolvedValue({ data: retrievedUserData });

  mockedAxios.post.mockReset();
  mockedAxios.post.mockResolvedValue({ data: {} });
};

describe('User Menu', () => {
  it('renders the user menu', async () => {
    mockRequests();
    renderUserMenu();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        userEndpoint,
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(screen.getByLabelText(labelProfile)).toBeInTheDocument();
    });

    userEvent.click(screen.getByLabelText(labelProfile));

    await waitFor(() => {
      expect(screen.getByText('admin')).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(screen.getByText('1:20 PM')).toBeInTheDocument();
    });
    expect(screen.getByText('January 1, 2022')).toBeInTheDocument();

    expect(screen.queryByText('Edit profile')).not.toBeInTheDocument();

    expect(screen.getByText(labelLogout)).toBeInTheDocument();
  });

  it('copies the autologin key when the corresponding button is clicked', async () => {
    mockRequestsWithAutologinKey();
    renderUserMenu();

    await waitFor(() => {
      expect(screen.getByLabelText(labelProfile)).toBeInTheDocument();
    });

    userEvent.click(screen.getByLabelText(labelProfile));

    await waitFor(() => {
      expect(screen.getByText(labelCopyAutologinLink)).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(screen.getByText('admin')).toBeInTheDocument();
    });

    userEvent.click(screen.getByText(labelCopyAutologinLink));

    expect(window.document.execCommand).toHaveBeenCalledWith('copy');
  });

  it(`logs out the user when the "${labelLogout}" button is clicked`, async () => {
    mockRequestsWithLogout();
    renderUserMenu();

    await waitFor(() => {
      expect(screen.getByLabelText(labelProfile)).toBeInTheDocument();
    });

    userEvent.click(screen.getByLabelText(labelProfile));

    await waitFor(() => {
      expect(screen.getByText(labelLogout)).toBeInTheDocument();
    });

    userEvent.click(screen.getByText(labelLogout));

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalledWith(
        logoutEndpoint,
        {},
        {
          ...cancelTokenRequestParam,
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
        },
      );
    });

    await waitFor(() => {
      expect(window.location.href).toBe('http://localhost/login');
    });
  });
});
