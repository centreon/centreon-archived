import * as React from 'react';

import axios from 'axios';
import { Provider as JotaiProvider } from 'jotai';
import { useAtomValue } from 'jotai/utils';

import { render, RenderResult, waitFor } from '@centreon/ui';
import {
  aclAtom,
  userAtom,
  downtimeAtom,
  acknowledgementAtom,
  refreshIntervalAtom,
} from '@centreon/ui-context';

import { cancelTokenRequestParam } from '../Resources/testUtils';

import Provider from '.';

jest.mock('@centreon/ui-context', () =>
  jest.requireActual('centreon-frontend/packages/ui-context'),
);

const mockedAxios = axios as jest.Mocked<typeof axios>;

const retrievedUser = {
  alias: 'Admin alias',
  is_export_button_enabled: true,
  locale: 'fr_FR.UTF8',
  name: 'Admin',
  timezone: 'Europe/Paris',
  use_deprecated_pages: false,
};

const contextUser = {
  alias: 'Admin alias',
  isExportButtonEnabled: true,
  locale: 'fr_FR.UTF8',
  name: 'Admin',
  timezone: 'Europe/Paris',
  use_deprecated_pages: false,
};

const retrievedDefaultParameters = {
  monitoring_default_acknowledgement_persistent: true,
  monitoring_default_acknowledgement_sticky: false,
  monitoring_default_downtime_duration: 1458,
  monitoring_default_downtime_fixed: true,
  monitoring_default_downtime_with_services: false,
  monitoring_default_refresh_interval: 15,
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

jest.mock('../App', () => {
  const ComponentWithUserContext = (): JSX.Element => {
    return <div />;
  };
  return {
    __esModule: true,
    default: ComponentWithUserContext,
  };
});

let atomsValue;

const TestComponent = (): JSX.Element => {
  const acl = useAtomValue(aclAtom);
  const user = useAtomValue(userAtom);
  const downtime = useAtomValue(downtimeAtom);
  const acknowledgement = useAtomValue(acknowledgementAtom);
  const refreshInterval = useAtomValue(refreshIntervalAtom);

  atomsValue = {
    acknowledgement,
    acl,
    downtime,
    refreshInterval,
    user,
  };

  return <div />;
};

const renderComponent = (): RenderResult => {
  return render(
    <JotaiProvider>
      <Provider>
        <TestComponent />
      </Provider>
    </JotaiProvider>,
  );
};

describe(Provider, () => {
  beforeEach(() => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: retrievedUser,
      })
      .mockResolvedValueOnce({
        data: retrievedDefaultParameters,
      })
      .mockResolvedValueOnce({
        data: retrievedTranslations,
      })
      .mockResolvedValueOnce({
        data: retrievedActionsAcl,
      });
  });

  it('populates the user atoms', async () => {
    renderComponent();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(4);
    });

    expect(mockedAxios.get).toHaveBeenNthCalledWith(
      1,
      './api/latest/configuration/users/current/parameters',
      cancelTokenRequestParam,
    );
    expect(mockedAxios.get).toHaveBeenNthCalledWith(
      2,
      './api/latest/administration/parameters',
      cancelTokenRequestParam,
    );
    expect(mockedAxios.get).toHaveBeenNthCalledWith(
      3,
      './api/internal.php?object=centreon_i18n&action=translation',
      cancelTokenRequestParam,
    );
    expect(mockedAxios.get).toHaveBeenNthCalledWith(
      4,
      './api/latest/users/acl/actions',
      cancelTokenRequestParam,
    );

    await waitFor(() => {
      expect(atomsValue.acl).toEqual({ actions: retrievedActionsAcl });
      expect(atomsValue.user).toEqual(contextUser);
      expect(atomsValue.downtime).toEqual({
        default_duration:
          retrievedDefaultParameters.monitoring_default_downtime_duration,
        default_fixed:
          retrievedDefaultParameters.monitoring_default_downtime_fixed,
        default_with_services:
          retrievedDefaultParameters.monitoring_default_downtime_with_services,
      });
      expect(atomsValue.refreshInterval).toEqual(
        retrievedDefaultParameters.monitoring_default_refresh_interval,
      );
      expect(atomsValue.acknowledgement).toEqual({
        persistent:
          retrievedDefaultParameters.monitoring_default_acknowledgement_persistent,
        sticky:
          retrievedDefaultParameters.monitoring_default_acknowledgement_sticky,
      });
    });
  });
});
