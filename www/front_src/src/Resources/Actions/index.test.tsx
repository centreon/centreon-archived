import * as React from 'react';

import mockDate from 'mockdate';
import axios from 'axios';
import { last, pick, map, head } from 'ramda';
import userEvent from '@testing-library/user-event';
import { Provider } from 'jotai';
import dayjs from 'dayjs';

import {
  render,
  RenderResult,
  waitFor,
  fireEvent,
  act,
  SeverityCode,
} from '@centreon/ui';
import {
  acknowledgementAtom,
  aclAtom,
  downtimeAtom,
  refreshIntervalAtom,
  userAtom,
} from '@centreon/ui-context';

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
  labelEndTime,
  labelEndDateGreaterThanStartDate,
  labelInvalidFormat,
  labelStartTime,
  labelSticky,
  labelForceActiveChecks,
  labelAcknowledgeWithSerivces,
} from '../translatedLabels';
import useLoadResources from '../Listing/useLoadResources';
import useListing from '../Listing/useListing';
import useFilter from '../testUtils/useFilter';
import Context, { ResourceContext } from '../testUtils/Context';
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

jest.mock('@centreon/ui-context', () =>
  jest.requireActual('centreon-frontend/packages/ui-context'),
);

const mockUser = {
  alias: 'admin',
  isExportButtonEnabled: true,
  locale: 'en',
  timezone: 'Europe/Paris',
};
const mockRefreshInterval = 15;
const mockDowntime = {
  default_duration: 7200,
  default_fixed: true,
  default_with_services: false,
};
const mockAcl = {
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
};
const mockAcknowledgement = {
  persistent: true,
  sticky: false,
};

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

const renderActions = (aclAtions = mockAcl): RenderResult => {
  return render(
    <Provider
      initialValues={[
        [userAtom, mockUser],
        [refreshIntervalAtom, mockRefreshInterval],
        [downtimeAtom, mockDowntime],
        [aclAtom, aclAtions],
        [acknowledgementAtom, mockAcknowledgement],
      ]}
    >
      <ActionsWithContext />
    </Provider>,
  );
};

describe(Actions, () => {
  const labelAcknowledgedByAdmin = `${labelAcknowledgedBy} admin`;
  const labelDowntimeByAdmin = `${labelDowntimeBy} admin`;

  const mockNow = '2020-01-01';

  beforeEach(() => {
    Object.defineProperty(window, 'matchMedia', {
      value: (query: string): MediaQueryList => ({
        addEventListener: (): void => undefined,

        addListener: (): void => undefined,

        dispatchEvent: (): boolean => true,
        // this is the media query that @material-ui/pickers uses to determine if a device is a desktop device
        matches: query === '(pointer: fine)',
        media: query,
        onchange: (): void => undefined,
        removeEventListener: (): void => undefined,
        removeListener: (): void => undefined,
      }),
      writable: true,
    });

    mockedAxios.post.mockReset();
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
  });

  afterEach(() => {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    delete window.matchMedia;

    mockDate.reset();
    mockedAxios.get.mockReset();
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

      userEvent.clear(commentField);

      await waitFor(() =>
        expect(
          last<HTMLElement>(getAllByText(labelConfirmAction)) as HTMLElement,
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
    const stickyCheckbox = await findByLabelText(labelSticky);
    const forceActiveChecks = await findByLabelText(labelForceActiveChecks);
    const acknowledgeAttachedResources = await findByLabelText(
      labelAcknowledgeWithSerivces,
    );

    fireEvent.click(notifyCheckbox);
    fireEvent.click(persistentCheckbox);
    fireEvent.click(stickyCheckbox);
    fireEvent.click(forceActiveChecks);
    fireEvent.click(acknowledgeAttachedResources);

    fireEvent.click(getByLabelText(labelAcknowledgeServices));

    mockedAxios.get.mockResolvedValueOnce({ data: {} });
    mockedAxios.post.mockResolvedValueOnce({});

    fireEvent.click(last(getAllByText(labelAcknowledge)) as HTMLElement);

    await waitFor(() =>
      expect(mockedAxios.post).toHaveBeenCalledWith(
        acknowledgeEndpoint,
        {
          acknowledgement: {
            comment: labelAcknowledgedByAdmin,
            force_active_checks: false,
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
    const { getByLabelText, getAllByText, getByText } = renderActions();

    const selectedResources = [host];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    fireEvent.click(getByLabelText(labelMoreActions).firstChild as HTMLElement);

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
    const { getByText, queryByText, getByLabelText } = renderActions();

    const selectedResources = [service];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    fireEvent.click(getByLabelText(labelMoreActions).firstChild as HTMLElement);

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
        last(getAllByText(labelSetDowntime)) as HTMLElement,
      ).toBeDisabled(),
    );
  });

  it('cannot send a downtime request when Downtime action is clicked and start date is greater than end date', async () => {
    const { getByLabelText, getAllByText, findByText, getByText } =
      renderActions();

    const selectedResources = [host];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    fireEvent.click(head(getAllByText(labelSetDowntime)) as HTMLElement);

    await findByText(labelDowntimeByAdmin);

    userEvent.clear(getByLabelText(labelEndTime));
    userEvent.type(getByLabelText(labelEndTime), dayjs(mockNow).format('L LT'));

    await waitFor(() =>
      expect(
        last(getAllByText(labelSetDowntime)) as HTMLElement,
      ).toBeDisabled(),
    );

    expect(getByText(labelEndDateGreaterThanStartDate)).toBeInTheDocument();
  });

  it('cannot send a downtime request when the Downtime action is clicked and the input dates have an invalid format', async () => {
    const {
      getByLabelText,
      getAllByText,
      findByText,
      getByText,
      findAllByText,
    } = renderActions();

    const selectedResources = [host];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    await findAllByText(labelSetDowntime);

    fireEvent.click(head(getAllByText(labelSetDowntime)) as HTMLElement);

    await findByText(labelDowntimeByAdmin);

    userEvent.type(getByLabelText(labelStartTime), '{backspace}l');

    await waitFor(() => {
      expect(
        last(getAllByText(labelSetDowntime)) as HTMLElement,
      ).toBeDisabled();
    });

    expect(getByText(labelInvalidFormat)).toBeInTheDocument();

    userEvent.type(getByLabelText(labelStartTime), '{backspace}M');

    await waitFor(() =>
      expect(last(getAllByText(labelSetDowntime)) as HTMLElement).toBeEnabled(),
    );

    userEvent.type(getByLabelText(labelEndTime), 'a');

    await waitFor(() =>
      expect(
        last(getAllByText(labelSetDowntime)) as HTMLElement,
      ).toBeDisabled(),
    );

    expect(getByText(labelInvalidFormat)).toBeInTheDocument();

    userEvent.type(getByLabelText(labelEndTime), '{backspace}');

    await waitFor(() =>
      expect(last(getAllByText(labelSetDowntime)) as HTMLElement).toBeEnabled(),
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
    mockedAxios.post.mockResolvedValueOnce({});

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
            with_services: false,
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
    mockedAxios.post.mockResolvedValueOnce({});

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
    mockedAxios.post.mockResolvedValueOnce({});

    const { getByText, getByLabelText } = renderActions();

    act(() => {
      context.setSelectedResources?.([service]);
    });

    fireEvent.click(
      getByLabelText(labelMoreActions).firstElementChild as HTMLElement,
    );

    fireEvent.click(getByText(labelSubmitStatus) as HTMLElement);

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

    fireEvent.click(
      getByLabelText(labelMoreActions).firstElementChild as HTMLElement,
    );

    fireEvent.click(getByText(labelSubmitStatus));

    userEvent.click(getByText(labelUp));

    await waitFor(() => {
      expect(getByText(labelDown)).toBeInTheDocument();
      expect(getByText(labelUnreachable)).toBeInTheDocument();
    });
  });

  it('cannot execute an action when associated ACL are not sufficient', async () => {
    const { getByText, getByLabelText } = renderActions({
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
    });

    const selectedResources = [host, service];

    act(() => {
      context.setSelectedResources?.(selectedResources);
    });

    await waitFor(() => {
      expect(getByText(labelCheck)).toBeDisabled();
      expect(getByText(labelAcknowledge)).toBeDisabled();
      expect(getByText(labelSetDowntime)).toBeDisabled();
    });

    fireEvent.click(getByLabelText(labelMoreActions).firstChild as HTMLElement);

    expect(getByText(labelDisacknowledge)).toHaveAttribute(
      'aria-disabled',
      'true',
    );
    expect(getByText(labelAddComment)).toHaveAttribute('aria-disabled', 'true');
  });

  const cannotDowntimeServicesAcl = {
    actions: {
      ...mockAcl.actions,
      service: {
        ...mockAcl.actions.service,
        downtime: false,
      },
    },
  };

  const cannotAcknowledgeServicesAcl = {
    actions: {
      ...mockAcl.actions,
      service: {
        ...mockAcl.actions.service,
        acknowledgement: false,
      },
    },
  };

  const cannotDisacknowledgeServicesAcl = {
    actions: {
      ...mockAcl.actions,
      service: {
        ...mockAcl.actions.service,
        disacknowledgement: false,
      },
    },
  };

  const cannotDowntimeHostsAcl = {
    actions: {
      ...mockAcl.actions,
      host: {
        ...mockAcl.actions.host,
        downtime: false,
      },
    },
  };

  const cannotAcknowledgeHostsAcl = {
    actions: {
      ...mockAcl.actions,
      host: {
        ...mockAcl.actions.host,
        acknowledgement: false,
      },
    },
  };

  const cannotDisacknowledgeHostsAcl = {
    actions: {
      ...mockAcl.actions,
      host: {
        ...mockAcl.actions.host,
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
      const { getByText, getByLabelText } = renderActions(acl);

      const selectedResources = [host, service];

      act(() => {
        context.setSelectedResources?.(selectedResources);
      });

      fireEvent.click(
        getByLabelText(labelMoreActions).firstChild as HTMLElement,
      );

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
      const { getByText, getByLabelText } = renderActions(acl);

      act(() => {
        context.setSelectedResources?.([host]);
      });

      fireEvent.click(
        getByLabelText(labelMoreActions).firstChild as HTMLElement,
      );

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
    const { getByText, getByLabelText } = renderActions({
      actions: {
        ...mockAcl.actions,
        host: {
          ...mockAcl.actions.host,
          submit_status: false,
        },
      },
    });

    act(() => {
      context.setSelectedResources?.([host, service]);
    });

    fireEvent.click(getByLabelText(labelMoreActions).firstChild as HTMLElement);

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
      expect(getByText(labelSubmitStatus)).not.toHaveAttribute('aria-disabled');
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
    const { getByText, getByLabelText } = renderActions({
      actions: {
        ...mockAcl.actions,
        host: {
          ...mockAcl.actions.host,
          comment: false,
        },
      },
    });

    act(() => {
      context.setSelectedResources?.([host, service]);
    });

    fireEvent.click(getByLabelText(labelMoreActions).firstChild as HTMLElement);

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
      expect(getByText(labelAddComment)).not.toHaveAttribute('aria-disabled');
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
      expect(getByText(labelAcknowledge)).toBeDisabled();
    });
  });
});
