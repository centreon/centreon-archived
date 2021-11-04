import * as React from 'react';

import mockDate from 'mockdate';
import axios from 'axios';
import { last, pick, map } from 'ramda';
import {
  render,
  RenderResult,
  waitFor,
  fireEvent,
  act,
} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Provider } from 'jotai';

import { useUserContext } from '@centreon/ui-context';
import { SeverityCode } from '@centreon/ui';

import {
  labelAcknowledgedBy,
  labelDowntimeBy,
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
  labelAcknowledge,
  labelSetDowntime,
  labelSetDowntimeOnServices,
  labelAcknowledgeServices,
  labelNotify,
  labelFixed,
  labelChangeEndDate,
  labelCheck,
  labelServicesDenied,
  labelHostsDenied,
  labelMoreActions,
  labelDisacknowledge,
  labelDisacknowledgeServices,
  labelSubmitStatus,
  labelUp,
  labelUnreachable,
  labelDown,
  labelOutput,
  labelPerformanceData,
  labelSubmit,
  labelOk,
  labelWarning,
  labelCritical,
  labelUnknown,
  labelAddComment,
  labelPersistent,
} from '../translatedLabels';
import useLoadResources from '../Listing/useLoadResources';
import useListing from '../Listing/useListing';
import useFilter from '../testUtils/useFilter';
import Context, { ResourceContext } from '../Context';
import { Resource } from '../models';
import useLoadDetails from '../testUtils/useLoadDetails';
import useDetails from '../Details/useDetails';
import useActions from '../testUtils/useActions';

import {
  acknowledgeEndpoint,
  downtimeEndpoint,
  checkEndpoint,
} from './api/endpoint';
import { disacknowledgeEndpoint } from './Resource/Disacknowledge/api';
import { submitStatusEndpoint } from './Resource/SubmitStatus/api';

import Actions from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const onRefresh = jest.fn();

jest.mock('react-redux', () => ({
  ...(jest.requireActual('react-redux') as jest.Mocked<unknown>),
  useSelector: jest.fn(),
}));

const mockUserContext = {
  acknowledgement: {
    persistent: true,
    sticky: false,
  },
  acl: {
    actions: {
      host: {
        acknowledgement: true,
        check: true,
        comment: true,
        disacknowledgement: true,
        downtime: true,
        submit_status: true,
      },
      service: {
        acknowledgement: true,
        check: true,
        comment: true,
        disacknowledgement: true,
        downtime: true,
        submit_status: true,
      },
    },
  },
  alias: 'admin',
  downtime: {
    default_duration: 7200,
  },
  locale: 'en',
  name: 'admin',

  refreshInterval: 15,
  timezone: 'Europe/Paris',
};

jest.mock('@centreon/centreon-frontend/packages/ui-context', () => ({
  ...(jest.requireActual('@centreon/ui-context') as jest.Mocked<unknown>),
  useUserContext: jest.fn(),
}));

const mockedUserContext = useUserContext as jest.Mock;

jest.mock('../icons/Downtime');

const ActionsWithLoading = (): JSX.Element => {
  useLoadResources();

  return <Actions onRefresh={onRefresh} />;
};

let context: ResourceContext;

const host = {
  id: 0,
  passive_checks: true,
  type: 'host',
} as Resource;

const service = {
  id: 1,
  parent: {
    id: 1,
  },
  passive_checks: true,
  type: 'service',
} as Resource;

const ActionsWithContext = (): JSX.Element => {
  const detailsState = useLoadDetails();
  const listingState = useListing();
  const actionsState = useActions();
  const filterState = useFilter();

  useDetails();

  context = {
    ...detailsState,
    ...listingState,
    ...actionsState,
    ...filterState,
  } as ResourceContext;

  return (
    <Context.Provider key="context" value={context}>
      <ActionsWithLoading />
    </Context.Provider>
  );
};

const ActionsWithJotai = (): JSX.Element => (
  <Provider>
    <ActionsWithContext />
  </Provider>
);

const renderActions = (): RenderResult => {
  return render(<ActionsWithJotai />);
};

describe(Actions, () => {
  const labelAcknowledgedByAdmin = `${labelAcknowledgedBy} admin`;
  const labelDowntimeByAdmin = `${labelDowntimeBy} admin`;

  const mockNow = '2020-01-01';

  beforeEach(() => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          meta: {
            limit: 30,
            page: 1,
            total: 0,
          },
          result: [],
        },
      })
      .mockResolvedValueOnce({ data: [] });

    mockDate.set(mockNow);

    mockedUserContext.mockReturnValue(mockUserContext);
  });

  afterEach(() => {
    mockDate.reset();
    mockedAxios.get.mockReset();

    mockedUserContext.mockReset();
  });

  it('executes a listing request when the refresh button is clicked', async () => {
    const { getByLabelText } = renderActions();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    mockedAxios.get.mockResolvedValueOnce({ data: {} });

    const refreshButton = getByLabelText(labelRefresh);

    await waitFor(() => expect(refreshButton).toBeEnabled());

    fireEvent.click(refreshButton.firstElementChild as HTMLElement);

    expect(onRefresh).toHaveBeenCalled();
  });

  it('swaps autorefresh icon when the icon is clicked', async () => {
    const { getByLabelText } = renderActions();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    fireEvent.click(
      getByLabelText(labelDisableAutorefresh).firstElementChild as HTMLElement,
    );

    expect(getByLabelText(labelEnableAutorefresh)).toBeTruthy();

    fireEvent.click(
      getByLabelText(labelEnableAutorefresh).firstElementChild as HTMLElement,
    );

    expect(getByLabelText(labelDisableAutorefresh)).toBeTruthy();
  });

  it.each([
    [labelAcknowledge, labelAcknowledgedByAdmin, labelAcknowledge],
    [labelSetDowntime, labelDowntimeByAdmin, labelSetDowntime],
  ])(
    'cannot send a %p request when the corresponding action is fired and the comment field is left empty',
    async (labelAction, labelComment, labelConfirmAction) => {
      const { getByText, getAllByText, findByText } = renderActions();

      const selectedResources = [host];

      act(() => {
        context.setSelectedResources?.(selectedResources);
      });

      await waitFor(() =>
        expect(context.selectedResources).toEqual(selectedResources),
      );

      fireEvent.click(getByText(labelAction));

      const commentField = await findByText(labelComment);

      fireEvent.change(commentField, {
        target: { value: '' },
      });

      await waitFor(() =>
        expect(
          (last<HTMLElement>(getAllByText(labelConfirmAction)) as HTMLElement)
            .parentElement,
        ).toBeDisabled(),
      );
    },
  );

  it('sends an acknowledgement request when Resources are selected and the Ackowledgement action is clicked and confirmed', async () => {
    const { getByText, getByLabelText, findByLabelText, getAllByText } =
      renderActions();

    const selectedResources = [host, service];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    fireEvent.click(getByText(labelAcknowledge));

    const notifyCheckbox = await findByLabelText(labelNotify);
    const persistentCheckbox = await findByLabelText(labelPersistent);

    fireEvent.click(notifyCheckbox);
    fireEvent.click(persistentCheckbox);
    fireEvent.click(getByLabelText(labelAcknowledgeServices));

    mockedAxios.get.mockResolvedValueOnce({ data: {} });
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    fireEvent.click(last(getAllByText(labelAcknowledge)) as HTMLElement);

    await waitFor(() =>
      expect(mockedAxios.post).toHaveBeenCalledWith(
        acknowledgeEndpoint,
        {
          acknowledgement: {
            comment: labelAcknowledgedByAdmin,
            is_notify_contacts: true,
            is_persistent_comment: false,
            is_sticky: false,
            with_services: true,
          },

          resources: map(pick(['type', 'id', 'parent']), selectedResources),
        },
        expect.anything(),
      ),
    );
  });

  it('sends a discknowledgement request when Resources are selected and the Disackowledgement action is clicked and confirmed', async () => {
    const { getByTitle, getAllByText, getByText } = renderActions();

    const selectedResources = [host];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    fireEvent.click(getByTitle(labelMoreActions).firstChild as HTMLElement);

    fireEvent.click(getByText(labelDisacknowledge));

    mockedAxios.delete.mockResolvedValueOnce({});

    fireEvent.click(last(getAllByText(labelDisacknowledge)) as HTMLElement);

    await waitFor(() =>
      expect(mockedAxios.delete).toHaveBeenCalledWith(disacknowledgeEndpoint, {
        cancelToken: expect.anything(),
        data: {
          disacknowledgement: {
            with_services: true,
          },

          resources: map(pick(['type', 'id', 'parent']), selectedResources),
        },
      }),
    );
  });

  it('does not display the "Acknowledge services attached to host" checkbox when only services are selected and the Acknowledge action is clicked', async () => {
    const { getByText, findByText, queryByText } = renderActions();

    const selectedResources = [service];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    fireEvent.click(getByText(labelAcknowledge));

    await findByText(labelAcknowledgedByAdmin);

    expect(queryByText(labelAcknowledgeServices)).toBeNull();
  });

  it('does not display the "Discknowledge services attached to host" checkbox when only services are selected and the Disacknowledge action is clicked', async () => {
    const { getByText, queryByText, getByTitle } = renderActions();

    const selectedResources = [service];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    fireEvent.click(getByTitle(labelMoreActions).firstChild as HTMLElement);

    fireEvent.click(getByText(labelDisacknowledge));

    await waitFor(() => {
      expect(queryByText(labelDisacknowledgeServices)).toBeNull();
    });
  });

  it('cannot send a downtime request when Downtime action is clicked, type is flexible and duration is empty', async () => {
    const { findByText, getAllByText, getByLabelText, getByDisplayValue } =
      renderActions();

    const selectedResources = [host];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    fireEvent.click(last(getAllByText(labelSetDowntime)) as HTMLElement);

    await findByText(labelDowntimeByAdmin);

    fireEvent.click(getByLabelText(labelFixed));
    fireEvent.change(getByDisplayValue('7200'), {
      target: { value: '' },
    });

    await waitFor(() =>
      expect(
        (last(getAllByText(labelSetDowntime)) as HTMLElement).parentElement,
      ).toBeDisabled(),
    );
  });

  it('cannot send a downtime request when Downtime action is clicked and start date is greater than end date', async () => {
    const { container, getByLabelText, getAllByText, findByText } =
      renderActions();

    const selectedResources = [host];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    await waitFor(() =>
      expect(last(getAllByText(labelSetDowntime))).toBeEnabled(),
    );

    fireEvent.click(last(getAllByText(labelSetDowntime)) as HTMLElement);

    await findByText(labelDowntimeByAdmin);

    // set previous day as end date using left arrow key
    fireEvent.click(getByLabelText(labelChangeEndDate));
    fireEvent.keyDown(container, { code: 37, key: 'ArrowLeft' });
    fireEvent.keyDown(container, { code: 13, key: 'Enter' });

    await waitFor(() =>
      expect(
        (last(getAllByText(labelSetDowntime)) as HTMLElement).parentElement,
      ).toBeDisabled(),
    );
  });

  it('sends a downtime request when Resources are selected and the Downtime action is clicked and confirmed', async () => {
    const { findAllByText, getAllByText } = renderActions();

    const selectedResources = [host];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    fireEvent.click(last(getAllByText(labelSetDowntime)) as HTMLElement);

    mockedAxios.get.mockResolvedValueOnce({ data: {} });
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    await findAllByText(labelDowntimeByAdmin);

    fireEvent.click(last(getAllByText(labelSetDowntime)) as HTMLElement);

    await waitFor(() =>
      expect(mockedAxios.post).toHaveBeenCalledWith(
        downtimeEndpoint,
        {
          downtime: {
            comment: labelDowntimeByAdmin,
            duration: 7200,
            end_time: '2020-01-01T02:00:00Z',
            is_fixed: true,
            start_time: '2020-01-01T00:00:00Z',
            with_services: true,
          },
          resources: map(pick(['type', 'id', 'parent']), selectedResources),
        },
        expect.anything(),
      ),
    );
  });

  it('sends a check request when Resources are selected and the Check action is clicked', async () => {
    const { getByText } = renderActions();

    const selectedResources = [host, service];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    mockedAxios.get.mockResolvedValueOnce({ data: {} });
    mockedAxios.all.mockResolvedValueOnce([]);
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    fireEvent.click(getByText(labelCheck));

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalledWith(
        checkEndpoint,
        {
          resources: map(pick(['type', 'id', 'parent']), selectedResources),
        },
        expect.anything(),
      );
    });
  });

  it('sends a submit status request when a Resource is selected and the Submit status action is clicked', async () => {
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    const { getByText, getByLabelText, getByTitle } = renderActions();

    act(() => {
      context.setSelectedResources?.([service]);
    });

    fireEvent.click(getByTitle(labelMoreActions).firstChild as HTMLElement);

    fireEvent.click(getByText(labelSubmitStatus));

    userEvent.click(getByText(labelOk));

    await waitFor(() => {
      expect(getByText(labelWarning)).toBeInTheDocument();
      expect(getByText(labelCritical)).toBeInTheDocument();
      expect(getByText(labelUnknown)).toBeInTheDocument();
    });

    userEvent.click(getByText(labelWarning));

    const output = 'output';
    const performanceData = 'performance data';

    fireEvent.change(getByLabelText(labelOutput), {
      target: {
        value: output,
      },
    });

    fireEvent.change(getByLabelText(labelPerformanceData), {
      target: {
        value: performanceData,
      },
    });

    fireEvent.click(getByText(labelSubmit));

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalledWith(
        submitStatusEndpoint,
        {
          resources: [
            {
              ...pick(['type', 'id', 'parent'], service),
              output,
              performance_data: performanceData,
              status: 1,
            },
          ],
        },
        expect.anything(),
      );
    });

    act(() => {
      context.setSelectedResources?.([host]);
    });

    fireEvent.click(getByTitle(labelMoreActions).firstChild as HTMLElement);

    fireEvent.click(getByText(labelSubmitStatus));

    userEvent.click(getByText(labelUp));

    await waitFor(() => {
      expect(getByText(labelDown)).toBeInTheDocument();
      expect(getByText(labelUnreachable)).toBeInTheDocument();
    });
  });

  it('cannot execute an action when associated ACL are not sufficient', async () => {
    mockedUserContext.mockReset().mockReturnValue({
      ...mockUserContext,
      acl: {
        actions: {
          host: {
            acknowledgement: false,
            check: false,
            comment: false,
            disacknowledgement: false,
            downtime: false,
            submit_status: false,
          },
          service: {
            acknowledgement: false,
            check: false,
            comment: false,
            disacknowledgement: false,
            downtime: false,
            submit_status: false,
          },
        },
      },
    });

    const { getByText, getByTitle } = renderActions();

    const selectedResources = [host, service];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    await waitFor(() => {
      expect(getByText(labelCheck).parentElement).toBeDisabled();
      expect(getByText(labelAcknowledge).parentElement).toBeDisabled();
      expect(getByText(labelSetDowntime).parentElement).toBeDisabled();
    });

    fireEvent.click(getByTitle(labelMoreActions).firstChild as HTMLElement);

    expect(getByText(labelDisacknowledge)).toHaveAttribute(
      'aria-disabled',
      'true',
    );
    expect(getByText(labelAddComment)).toHaveAttribute('aria-disabled', 'true');
  });

  const cannotDowntimeServicesAcl = {
    actions: {
      ...mockUserContext.acl.actions,
      service: {
        ...mockUserContext.acl.actions.service,
        downtime: false,
      },
    },
  };

  const cannotAcknowledgeServicesAcl = {
    actions: {
      ...mockUserContext.acl.actions,
      service: {
        ...mockUserContext.acl.actions.service,
        acknowledgement: false,
      },
    },
  };

  const cannotDisacknowledgeServicesAcl = {
    actions: {
      ...mockUserContext.acl.actions,
      service: {
        ...mockUserContext.acl.actions.service,
        disacknowledgement: false,
      },
    },
  };

  const cannotDowntimeHostsAcl = {
    actions: {
      ...mockUserContext.acl.actions,
      host: {
        ...mockUserContext.acl.actions.host,
        downtime: false,
      },
    },
  };

  const cannotAcknowledgeHostsAcl = {
    actions: {
      ...mockUserContext.acl.actions,
      host: {
        ...mockUserContext.acl.actions.host,
        acknowledgement: false,
      },
    },
  };

  const cannotDisacknowledgeHostsAcl = {
    actions: {
      ...mockUserContext.acl.actions,
      host: {
        ...mockUserContext.acl.actions.host,
        disacknowledgement: false,
      },
    },
  };

  it.each([
    [
      labelSetDowntime,
      labelSetDowntime,
      labelServicesDenied,
      cannotDowntimeServicesAcl,
    ],
    [
      labelAcknowledge,
      labelAcknowledge,
      labelServicesDenied,
      cannotAcknowledgeServicesAcl,
    ],
    [
      labelSetDowntime,
      labelSetDowntime,
      labelHostsDenied,
      cannotDowntimeHostsAcl,
    ],
    [
      labelAcknowledge,
      labelAcknowledge,
      labelHostsDenied,
      cannotAcknowledgeHostsAcl,
    ],
    [
      labelDisacknowledge,
      labelDisacknowledge,
      labelHostsDenied,
      cannotDisacknowledgeHostsAcl,
    ],
  ])(
    'displays a warning message when trying to %p with limited ACL',
    async (_, labelAction, labelAclWarning, acl) => {
      mockedUserContext.mockReset().mockReturnValue({
        ...mockUserContext,
        acl,
      });

      const { getByText, getByTitle } = renderActions();

      const selectedResources = [host, service];

      act(() => {
        context.setSelectedResources?.(selectedResources);
      });

      fireEvent.click(getByTitle(labelMoreActions).firstChild as HTMLElement);

      fireEvent.click(getByText(labelAction));

      await waitFor(() => {
        expect(getByText(labelAclWarning)).toBeInTheDocument();
      });
    },
  );

  it.each([
    [
      labelSetDowntime,
      labelSetDowntime,
      labelSetDowntimeOnServices,
      cannotDowntimeServicesAcl,
    ],
    [
      labelAcknowledge,
      labelAcknowledge,
      labelAcknowledgeServices,
      cannotAcknowledgeServicesAcl,
    ],
    [
      labelDisacknowledge,
      labelDisacknowledge,
      labelDisacknowledgeServices,
      cannotDisacknowledgeServicesAcl,
    ],
  ])(
    'disables services propagation option when trying to %p on hosts when ACL on services are not sufficient',
    async (_, labelAction, labelAppliesOnServices, acl) => {
      mockedUserContext.mockReset().mockReturnValue({
        ...mockUserContext,
        acl,
      });

      const { getByText, getByTitle } = renderActions();

      act(() => {
        context.setSelectedResources?.([host]);
      });

      fireEvent.click(getByTitle(labelMoreActions).firstChild as HTMLElement);

      fireEvent.click(getByText(labelAction));

      await waitFor(() => {
        expect(
          getByText(labelAppliesOnServices).parentElement?.querySelector(
            'input[type="checkbox"]',
          ),
        ).toBeDisabled();
      });
    },
  );

  it('disables the submit status action when one of the following condition is met: ACL are not sufficient, more than one resource is selected, selected resource is not passive', async () => {
    const { getByText, getByTitle } = renderActions();

    mockedUserContext.mockReset().mockReturnValue({
      ...mockUserContext,
      acl: {
        actions: {
          ...mockUserContext.acl.actions,
          host: {
            ...mockUserContext.acl.actions.host,
            submit_status: false,
          },
        },
      },
    });

    act(() => {
      context.setSelectedResources?.([host, service]);
    });

    fireEvent.click(getByTitle(labelMoreActions).firstChild as HTMLElement);

    await waitFor(() => {
      expect(getByText(labelSubmitStatus)).toHaveAttribute(
        'aria-disabled',
        'true',
      );
    });

    act(() => {
      context.setSelectedResources?.([host]);
    });

    await waitFor(() => {
      expect(getByText(labelSubmitStatus)).toHaveAttribute(
        'aria-disabled',
        'true',
      );
    });

    act(() => {
      context.setSelectedResources?.([service]);
    });

    await waitFor(() => {
      expect(getByText(labelSubmitStatus)).toHaveAttribute(
        'aria-disabled',
        'false',
      );
    });

    act(() => {
      context.setSelectedResources?.([{ ...service, passive_checks: false }]);
    });

    await waitFor(() => {
      expect(getByText(labelSubmitStatus)).toHaveAttribute(
        'aria-disabled',
        'true',
      );
    });
  });

  it('disables the comment action when the ACL are not sufficient or more than one resource is selected', async () => {
    const { getByText, getByTitle } = renderActions();

    mockedUserContext.mockReset().mockReturnValue({
      ...mockUserContext,
      acl: {
        actions: {
          ...mockUserContext.acl.actions,
          host: {
            ...mockUserContext.acl.actions.host,
            comment: false,
          },
        },
      },
    });

    act(() => {
      context.setSelectedResources?.([host, service]);
    });

    fireEvent.click(getByTitle(labelMoreActions).firstChild as HTMLElement);

    await waitFor(() => {
      expect(getByText(labelAddComment)).toHaveAttribute(
        'aria-disabled',
        'true',
      );
    });

    act(() => {
      context.setSelectedResources?.([host]);
    });

    await waitFor(() => {
      expect(getByText(labelAddComment)).toHaveAttribute(
        'aria-disabled',
        'true',
      );
    });

    act(() => {
      context.setSelectedResources?.([service]);
    });

    await waitFor(() => {
      expect(getByText(labelAddComment)).toHaveAttribute(
        'aria-disabled',
        'false',
      );
    });
  });

  it('disables the acknowledge action when selected resources have an OK or UP status', async () => {
    const { getByText } = renderActions();

    act(() => {
      context.setSelectedResources?.([
        {
          ...host,
          status: {
            name: 'UP',
            severity_code: SeverityCode.Ok,
          },
        },
        {
          ...service,
          status: {
            name: 'OK',
            severity_code: SeverityCode.Ok,
          },
        },
      ]);
    });

    await waitFor(() => {
      expect(getByText(labelAcknowledge).parentElement).toBeDisabled();
    });
  });
});
