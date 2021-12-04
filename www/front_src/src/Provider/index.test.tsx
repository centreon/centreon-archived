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

import Provider from '.';

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
  monitoring_default_downtime_with_services: true,
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
  return render(<Provider />);
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

  it('populates the userContext', async () => {
    renderComponent();

    await waitFor(() => {
      expect(useAcl().setActionAcl).toHaveBeenCalledWith(retrievedActionsAcl);
      expect(useUser().setUser).toHaveBeenCalledWith(contextUser);
      expect(useDowntime().setDowntime).toHaveBeenCalledWith({
        default_duration:
          retrievedDefaultParameters.monitoring_default_downtime_duration,
        downtime_with_services:
          retrievedDefaultParameters.monitoring_default_downtime_with_services,
        fixed: retrievedDefaultParameters.monitoring_default_downtime_fixed,
      });
    });
    expect(useRefreshInterval().setRefreshInterval).toHaveBeenCalledWith(
      retrievedDefaultParameters.monitoring_default_refresh_interval,
    );

    expect(useAcknowledgement().setAcknowledgement).toHaveBeenCalledWith({
      persistent:
        retrievedDefaultParameters.monitoring_default_acknowledgement_persistent,
      sticky:
        retrievedDefaultParameters.monitoring_default_acknowledgement_sticky,
    });
  });
});
