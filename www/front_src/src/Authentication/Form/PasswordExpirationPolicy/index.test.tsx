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

import { SecurityPolicy, SecurityPolicyFromAPI } from '../../models';
import useValidationSchema from '../../useValidationSchema';
import {
  defaultSecurityPolicy,
  securityPolicyWithInvalidDelayBeforeNewPassword,
  securityPolicyWithInvalidPasswordExpiration,
} from '../defaults';
import {
  labelCanReuseLast3Passwords,
  labelChooseADurationBetween7DaysAnd12Months,
  labelChooseADurationBetween1HourAnd1Week,
  labelDays,
  labelHour,
  labelMonth,
  labelPasswordExpiration,
  labelPasswordExpirationPolicy,
  labelTimeBeforeSettingNewPassword,
  labelExcludedUsers,
} from '../../translatedLabels';
import { contactsEndpoint } from '../../api/endpoints';

import PasswordExpirationPolicy from '.';

const noOp = jest.fn();

const mockedAxios = axios as jest.Mocked<typeof axios>;

const cancelTokenRequestParam = { cancelToken: {} };

interface Props {
  initialValues: SecurityPolicy;
}

const TestComponent = ({ initialValues }: Props): JSX.Element => {
  const validationSchema = useValidationSchema();

  return (
    <Formik<SecurityPolicy>
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
  initialValues: SecurityPolicyFromAPI = defaultSecurityPolicy,
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

    expect(screen.getByText(labelPasswordExpiration)).toBeInTheDocument();

    expect(
      screen.getByLabelText(`${labelPasswordExpiration} ${labelMonth}`),
    ).toBeInTheDocument();

    expect(screen.getByText(labelMonth)).toBeInTheDocument();

    expect(
      screen.getByLabelText(`${labelPasswordExpiration} ${labelDays}`),
    ).toHaveTextContent('7');

    expect(screen.getByText(labelDays)).toBeInTheDocument();

    expect(
      screen.getByText(labelTimeBeforeSettingNewPassword),
    ).toBeInTheDocument();

    expect(
      screen.getByLabelText(
        `${labelTimeBeforeSettingNewPassword} ${labelHour}`,
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
    expect(screen.getByText(labelPasswordExpiration)).toBeInTheDocument();
    userEvent.type(
      screen.getByLabelText(`${labelPasswordExpiration} ${labelDays}`),
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
      screen.getByText(labelTimeBeforeSettingNewPassword),
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
      screen.getByText(labelTimeBeforeSettingNewPassword),
    ).toBeInTheDocument();

    userEvent.type(
      screen.getByLabelText(
        `${labelTimeBeforeSettingNewPassword} ${labelHour}`,
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
    expect(screen.getByText(labelCanReuseLast3Passwords)).toBeInTheDocument();

    userEvent.click(screen.getByLabelText(labelCanReuseLast3Passwords));

    await waitFor(() => {
      expect(screen.getByLabelText(labelCanReuseLast3Passwords)).toBeChecked();
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

    userEvent.click(screen.getByText('admin'));

    userEvent.keyboard('{Escape}');

    expect(screen.getAllByText('admin')).toHaveLength(1);
  });
});
