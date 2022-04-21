import * as React from 'react';

import axios from 'axios';
import { render, RenderResult, waitFor } from '@testing-library/react';

import {
  useUser,
  useAcl,
  useDowntime,
  useRefreshInterval,
  useAcknowledgement,
} from '@centreon/ui-context';

import AppProvider from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const retrievedUser = {
  alias: 'Admin alias',
  locale: 'fr_FR.UTF8',
  name: 'Admin',
  timezone: 'Europe/Paris',
  use_deprecated_pages: false,
};

const retrievedDefaultParameters = {
  monitoring_default_acknowledgement_force_active_checks: false,
  monitoring_default_acknowledgement_notify: false,
  monitoring_default_acknowledgement_persistent: true,
  monitoring_default_acknowledgement_sticky: false,
  monitoring_default_acknowledgement_with_services: false,
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
        data: retrievedDefaultParameters,
      })
      .mockResolvedValueOnce({
        data: retrievedTranslations,
      })
      .mockResolvedValueOnce({
        data: retrievedActionsAcl,
      });
  });

  it('populates the userContext', async () => {
    renderComponent();

    await waitFor(() => {
      expect(useAcl().setActionAcl).toHaveBeenCalledWith(retrievedActionsAcl);
      expect(useUser().setUser).toHaveBeenCalledWith(retrievedUser);
      expect(useDowntime().setDowntime).toHaveBeenCalledWith({
        duration:
          retrievedDefaultParameters.monitoring_default_downtime_duration,
        fixed: retrievedDefaultParameters.monitoring_default_downtime_fixed,
        with_services:
          retrievedDefaultParameters.monitoring_default_downtime_with_services,
      });
      expect(useRefreshInterval().setRefreshInterval).toHaveBeenCalledWith(
        retrievedDefaultParameters.monitoring_default_refresh_interval,
      );
      expect(useAcknowledgement().setAcknowledgement).toHaveBeenCalledWith({
        force_active_checks:
          retrievedDefaultParameters.monitoring_default_acknowledgement_force_active_checks,
        notify:
          retrievedDefaultParameters.monitoring_default_acknowledgement_notify,
        persistent:
          retrievedDefaultParameters.monitoring_default_acknowledgement_persistent,
        sticky:
          retrievedDefaultParameters.monitoring_default_acknowledgement_sticky,
        with_services:
          retrievedDefaultParameters.monitoring_default_acknowledgement_with_services,
      });
    });
  });
});
