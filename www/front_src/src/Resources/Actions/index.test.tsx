import * as React from 'react';

import mockDate from 'mockdate';
import axios from 'axios';
import { useSelector } from 'react-redux';
import { last, pick, map } from 'ramda';
import {
  render,
  RenderResult,
  waitFor,
  fireEvent,
  act,
} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import { useUserContext } from '@centreon/ui-context';

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
} from '../translatedLabels';
import useLoadResources from '../Listing/useLoadResources';
import useListing from '../Listing/useListing';
import useFilter from '../Filter/useFilter';
import Context, { ResourceContext } from '../Context';
import { mockAppStateSelector } from '../testUtils';
import { Resource } from '../models';
import useDetails from '../Details/useDetails';

import {
  acknowledgeEndpoint,
  downtimeEndpoint,
  checkEndpoint,
} from './api/endpoint';
import useActions from './useActions';
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
  alias: 'admin',
  name: 'admin',
  locale: 'en',
  timezone: 'Europe/Paris',

  acl: {
    actions: {
      service: {
        downtime: true,
        acknowledgement: true,
        disacknowledgement: true,
        check: true,
        submit_status: true,
        comment: true,
      },
      host: {
        downtime: true,
        acknowledgement: true,
        disacknowledgement: true,
        check: true,
        submit_status: true,
        comment: true,
      },
    },
  },

  downtime: {
    default_duration: 7200,
  },
  refresh_interval: 15,
};

jest.mock('@centreon/ui-context', () => ({
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
  type: 'host',
  id: 0,
  passive_checks: true,
} as Resource;

const service = {
  id: 1,
  type: 'service',
  parent: {
    id: 1,
  },
  passive_checks: true,
} as Resource;

const ActionsWithContext = (): JSX.Element => {
  const detailsState = useDetails();
  const listingState = useListing();
  const actionsState = useActions();
  const filterState = useFilter();

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

const renderActions = (): RenderResult => {
  return render(<ActionsWithContext />);
};

describe(Actions, () => {
  const labelAcknowledgedByAdmin = `${labelAcknowledgedBy} admin`;
  const labelDowntimeByAdmin = `${labelDowntimeBy} admin`;

  const mockNow = '2020-01-01';

  beforeEach(() => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          result: [],
          meta: {
            page: 1,
            limit: 30,
            total: 0,
          },
        },
      })
      .mockResolvedValueOnce({ data: [] });

    mockDate.set(mockNow);
    mockAppStateSelector(useSelector);

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
        context.setSelectedResources(selectedResources);
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
          (last(getAllByText(labelConfirmAction)) as HTMLElement).parentElement,
        ).toBeDisabled(),
      );
    },
  );

  it('sends an acknowledgement request when Resources are selected and the Ackowledgement action is clicked and confirmed', async () => {
    const {
      getByText,
      getByLabelText,
      findByLabelText,
      getAllByText,
    } = renderActions();

    const selectedResources = [host, service];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    fireEvent.click(getByText(labelAcknowledge));

    const notifyCheckbox = await findByLabelText(labelNotify);

    fireEvent.click(notifyCheckbox);
    fireEvent.click(getByLabelText(labelAcknowledgeServices));

    mockedAxios.get.mockResolvedValueOnce({ data: {} });
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    fireEvent.click(last(getAllByText(labelAcknowledge)) as HTMLElement);

    await waitFor(() =>
      expect(mockedAxios.post).toHaveBeenCalledWith(
        acknowledgeEndpoint,
        {
          resources: map(pick(['type', 'id', 'parent']), selectedResources),

          acknowledgement: {
            comment: labelAcknowledgedByAdmin,
            is_notify_contacts: true,
            with_services: true,
          },
        },
        expect.anything(),
      ),
    );
  });

  it('sends a discknowledgement request when Resources are selected and the Disackowledgement action is clicked and confirmed', async () => {
    const { getByText, getAllByText } = renderActions();

    const selectedResources = [host];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    fireEvent.click(getByText(labelDisacknowledge));

    mockedAxios.delete.mockResolvedValueOnce({});

    fireEvent.click(last(getAllByText(labelDisacknowledge)) as HTMLElement);

    await waitFor(() =>
      expect(mockedAxios.delete).toHaveBeenCalledWith(disacknowledgeEndpoint, {
        cancelToken: expect.anything(),
        data: {
          resources: map(pick(['type', 'id', 'parent']), selectedResources),

          disacknowledgement: {
            with_services: true,
          },
        },
      }),
    );
  });

  it('does not display the "Acknowledge services attached to host" checkbox when only services are selected and the Acknowledge action is clicked', async () => {
    const { getByText, findByText, queryByText } = renderActions();

    const selectedResources = [service];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    fireEvent.click(getByText(labelAcknowledge));

    await findByText(labelAcknowledgedByAdmin);

    expect(queryByText(labelAcknowledgeServices)).toBeNull();
  });

  it('does not display the "Discknowledge services attached to host" checkbox when only services are selected and the Disacknowledge action is clicked', async () => {
    const { getByText, queryByText } = renderActions();

    const selectedResources = [service];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    fireEvent.click(getByText(labelDisacknowledge));

    await waitFor(() => {
      expect(queryByText(labelDisacknowledgeServices)).toBeNull();
    });
  });

  it('cannot send a downtime request when Downtime action is clicked, type is flexible and duration is empty', async () => {
    const {
      findByText,
      getAllByText,
      getByLabelText,
      getByDisplayValue,
    } = renderActions();

    const selectedResources = [host];

    act(() => {
      context.setSelectedResources(selectedResources);
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
    const {
      container,
      getByLabelText,
      getAllByText,
      findByText,
    } = renderActions();

    const selectedResources = [host];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    await waitFor(() =>
      expect(last(getAllByText(labelSetDowntime))).toBeEnabled(),
    );

    fireEvent.click(last(getAllByText(labelSetDowntime)) as HTMLElement);

    await findByText(labelDowntimeByAdmin);

    // set previous day as end date using left arrow key
    fireEvent.click(getByLabelText(labelChangeEndDate));
    fireEvent.keyDown(container, { key: 'ArrowLeft', code: 37 });
    fireEvent.keyDown(container, { key: 'Enter', code: 13 });

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
      context.setSelectedResources(selectedResources);
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
          resources: map(pick(['type', 'id', 'parent']), selectedResources),
          downtime: {
            comment: labelDowntimeByAdmin,
            duration: 7200,
            start_time: '2020-01-01T00:00:00Z',
            end_time: '2020-01-01T02:00:00Z',
            is_fixed: true,
            with_services: true,
          },
        },
        expect.anything(),
      ),
    );
  });

  it('sends a check request when Resources are selected and the Check action is clicked', async () => {
    const { getByText } = renderActions();

    const selectedResources = [host, service];

    act(() => {
      context.setSelectedResources(selectedResources);
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

    const { getByText, getByLabelText } = renderActions();

    act(() => {
      context.setSelectedResources([service]);
    });

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
              status: 1,
              output,
              performance_data: performanceData,
            },
          ],
        },
        expect.anything(),
      );
    });

    act(() => {
      context.setSelectedResources([host]);
    });

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
          service: {
            downtime: false,
            check: false,
            acknowledgement: false,
            disacknowledgement: false,
            submit_status: false,
            comment: false,
          },
          host: {
            downtime: false,
            check: false,
            acknowledgement: false,
            disacknowledgement: false,
            submit_status: false,
            comment: false,
          },
        },
      },
    });

    const { getByText } = renderActions();

    const selectedResources = [host, service];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    await waitFor(() => {
      expect(getByText(labelCheck).parentElement).toBeDisabled();
      expect(getByText(labelAcknowledge).parentElement).toBeDisabled();
      expect(getByText(labelSetDowntime).parentElement).toBeDisabled();
    });

    fireEvent.click(getByText(labelMoreActions));

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

      const { getByText } = renderActions();

      const selectedResources = [host, service];

      act(() => {
        context.setSelectedResources(selectedResources);
      });

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

      const { getByText } = renderActions();

      act(() => {
        context.setSelectedResources([host]);
      });

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
    const { getByText } = renderActions();

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
      context.setSelectedResources([host, service]);
    });

    await waitFor(() => {
      expect(getByText(labelSubmitStatus)).toHaveAttribute(
        'aria-disabled',
        'true',
      );
    });

    act(() => {
      context.setSelectedResources([host]);
    });

    await waitFor(() => {
      expect(getByText(labelSubmitStatus)).toHaveAttribute(
        'aria-disabled',
        'true',
      );
    });

    act(() => {
      context.setSelectedResources([service]);
    });

    await waitFor(() => {
      expect(getByText(labelSubmitStatus)).toHaveAttribute(
        'aria-disabled',
        'false',
      );
    });

    act(() => {
      context.setSelectedResources([{ ...service, passive_checks: false }]);
    });

    await waitFor(() => {
      expect(getByText(labelSubmitStatus)).toHaveAttribute(
        'aria-disabled',
        'true',
      );
    });
  });

  it('disables the comment action when the ACL are not sufficient or more than one resource is selected', async () => {
    const { getByText } = renderActions();

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
      context.setSelectedResources([host, service]);
    });

    await waitFor(() => {
      expect(getByText(labelAddComment)).toHaveAttribute(
        'aria-disabled',
        'true',
      );
    });

    act(() => {
      context.setSelectedResources([host]);
    });

    await waitFor(() => {
      expect(getByText(labelAddComment)).toHaveAttribute(
        'aria-disabled',
        'true',
      );
    });

    act(() => {
      context.setSelectedResources([service]);
    });

    await waitFor(() => {
      expect(getByText(labelAddComment)).toHaveAttribute(
        'aria-disabled',
        'false',
      );
    });
  });
});
