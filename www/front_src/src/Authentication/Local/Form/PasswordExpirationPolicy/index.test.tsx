import * as React from 'react';

import userEvent from '@testing-library/user-event';
import { Formik } from 'formik';
import axios from 'axios';

import {
  render,
  RenderResult,
  screen,
  waitFor,
  buildListingEndpoint,
} from '@centreon/ui';

import {
  PasswordSecurityPolicy,
  PasswordSecurityPolicyFromAPI,
} from '../../models';
import useValidationSchema from '../../useValidationSchema';
import {
  defaultPasswordSecurityPolicy,
  securityPolicyWithInvalidDelayBeforeNewPassword,
  securityPolicyWithInvalidPasswordExpiration,
} from '../defaults';
import {
  labelLast3PasswordsCanBeReused,
  labelChooseADurationBetween7DaysAnd12Months,
  labelChooseADurationBetween1HourAnd1Week,
  labelDays,
  labelHour,
  labelMonth,
  labelPasswordExpiresAfter,
  labelPasswordExpirationPolicy,
  labelMinimumTimeBetweenPasswordChanges,
  labelExcludedUsers,
} from '../../translatedLabels';
import { contactsEndpoint } from '../../../api/endpoints';

import PasswordExpirationPolicy from '.';

const noOp = jest.fn();

const mockedAxios = axios as jest.Mocked<typeof axios>;

const cancelTokenRequestParam = { cancelToken: {} };

interface Props {
  initialValues: PasswordSecurityPolicy;
}

const TestComponent = ({ initialValues }: Props): JSX.Element => {
  const validationSchema = useValidationSchema();

  return (
    <Formik<PasswordSecurityPolicy>
      enableReinitialize
      validateOnBlur
      validateOnMount
      initialValues={initialValues}
      validationSchema={validationSchema}
      onSubmit={noOp}
    >
      {(): JSX.Element => <PasswordExpirationPolicy />}
    </Formik>
  );
};

const renderPasswordExpirationPolicy = (
  initialValues: PasswordSecurityPolicyFromAPI = defaultPasswordSecurityPolicy,
): RenderResult =>
  render(
    <TestComponent initialValues={initialValues.password_security_policy} />,
  );

const retrievedContacts = {
  meta: {
    limit: 10,
    page: 1,
    search: {},
    sort_by: {},
    total: 2,
  },
  result: [
    {
      alias: 'admin',
      email: 'admin@admin.com',
      id: 1,
      is_admin: true,
    },
    {
      alias: 'user',
      email: 'user@admin.com',
      id: 2,
      is_admin: false,
    },
  ],
};

describe('Password expiration policy', () => {
  beforeEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.get.mockResolvedValue({
      data: retrievedContacts,
    });
  });

  it('renders the password expiration policy fields with values', async () => {
    renderPasswordExpirationPolicy();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });

    expect(screen.getByText(labelPasswordExpiresAfter)).toBeInTheDocument();

    expect(
      screen.getByLabelText(`${labelPasswordExpiresAfter} ${labelMonth}`),
    ).toBeInTheDocument();

    expect(screen.getByText(labelMonth)).toBeInTheDocument();

    expect(
      screen.getByLabelText(`${labelPasswordExpiresAfter} ${labelDays}`),
    ).toHaveTextContent('7');

    expect(screen.getByText(labelDays)).toBeInTheDocument();

    expect(
      screen.getByText(labelMinimumTimeBetweenPasswordChanges),
    ).toBeInTheDocument();

    expect(
      screen.getByLabelText(
        `${labelMinimumTimeBetweenPasswordChanges} ${labelHour}`,
      ),
    ).toHaveTextContent('1');

    expect(screen.getByText(labelExcludedUsers)).toBeInTheDocument();
  });

  it('does not display any error message when the password expiration time is cleared', async () => {
    renderPasswordExpirationPolicy();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });
    expect(screen.getByText(labelPasswordExpiresAfter)).toBeInTheDocument();
    userEvent.type(
      screen.getByLabelText(`${labelPasswordExpiresAfter} ${labelDays}`),
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(
        screen.queryByText(labelChooseADurationBetween7DaysAnd12Months),
      ).not.toBeInTheDocument();
    });
  });

  it('displays an error message when the delay before new password time is outside the bounds', async () => {
    renderPasswordExpirationPolicy({
      password_security_policy: securityPolicyWithInvalidDelayBeforeNewPassword,
    });

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });
    expect(
      screen.getByText(labelMinimumTimeBetweenPasswordChanges),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(
        screen.getByText(labelChooseADurationBetween1HourAnd1Week),
      ).toBeInTheDocument();
    });

    renderPasswordExpirationPolicy({
      password_security_policy: securityPolicyWithInvalidPasswordExpiration,
    });

    await waitFor(() => {
      expect(
        screen.getByText(labelChooseADurationBetween7DaysAnd12Months),
      ).toBeInTheDocument();
    });
  });

  it('does not display any error message when the delay before new password time is cleared', async () => {
    renderPasswordExpirationPolicy();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });
    expect(
      screen.getByText(labelMinimumTimeBetweenPasswordChanges),
    ).toBeInTheDocument();

    userEvent.type(
      screen.getByLabelText(
        `${labelMinimumTimeBetweenPasswordChanges} ${labelHour}`,
      ),
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(
        screen.queryByText(labelChooseADurationBetween1HourAnd1Week),
      ).not.toBeInTheDocument();
    });
  });

  it('selects the "Can reuse passwords" field when clicking on the corresponding switch', async () => {
    renderPasswordExpirationPolicy();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });
    expect(
      screen.getByText(labelLast3PasswordsCanBeReused),
    ).toBeInTheDocument();

    userEvent.click(screen.getByLabelText(labelLast3PasswordsCanBeReused));

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelLast3PasswordsCanBeReused),
      ).toBeChecked();
    });
  });

  it('updates the excluded users field when an user is selected from the retrieved options', async () => {
    renderPasswordExpirationPolicy();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });
    userEvent.click(screen.getByLabelText(labelExcludedUsers));

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        buildListingEndpoint({
          baseEndpoint: contactsEndpoint,
          parameters: {
            page: 1,
            search: {
              conditions: [
                {
                  field: 'provider_name',
                  values: {
                    $eq: 'local',
                  },
                },
              ].filter(Boolean),
            },
            sort: { alias: 'ASC' },
          },
        }),
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(screen.getByText('admin')).toBeInTheDocument();
    });

    userEvent.click(screen.getByText('admin'));

    userEvent.keyboard('{Escape}');

    expect(screen.getAllByText('admin')).toHaveLength(1);
  });
});
