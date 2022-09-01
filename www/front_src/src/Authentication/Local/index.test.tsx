import axios from 'axios';
import userEvent from '@testing-library/user-event';

import {
  RenderResult,
  render,
  screen,
  waitFor,
  buildListingEndpoint,
  TestQueryProvider,
  resetMocks,
  mockResponseOnce,
  getFetchCall,
} from '@centreon/ui';

import {
  authenticationProvidersEndpoint,
  contactsEndpoint,
} from '../api/endpoints';
import { Provider } from '../models';

import {
  labelReset,
  labelDefinePasswordPasswordSecurityPolicy,
  labelDoYouWantToResetTheForm,
  labelNumberOfAttemptsBeforeUserIsBlocked,
  labelPasswordBlockingPolicy,
  labelPasswordCasePolicy,
  labelPasswordExpirationPolicy,
  labelMinimumPasswordLength,
  labelResetTheForm,
  labelSave,
  labelPasswordMustContainLowerCase,
  labelPasswordMustContainUpperCase,
  labelPasswordMustContainNumbers,
  labelPasswordMustContainSpecialCharacters,
  labelStrong,
  labelGood,
  labelWeak,
  labelPasswordExpiresAfter,
  labelMonth,
  labelDays,
  labelMinimumTimeBetweenPasswordChanges,
  labelHour,
  labelExcludedUsers,
  labelChooseADurationBetween7DaysAnd12Months,
  labelChooseADurationBetween1HourAnd1Week,
  labelLast3PasswordsCanBeReused,
  labelTimeThatMustPassBeforeNewConnection,
  labelMinutes,
  labelChooseAValueBetween1and10,
  labelThisWillNotBeUsedBecauseNumberOfAttemptsIsNotDefined,
  labelBlockingDurationMustBeLessThanOrEqualTo7Days,
  labelDay,
} from './translatedLabels';
import {
  defaultPasswordSecurityPolicyAPI,
  retrievedPasswordSecurityPolicyAPI,
  defaultPasswordSecurityPolicyWithNullValues,
  securityPolicyWithInvalidDelayBeforeNewPassword,
  securityPolicyWithInvalidPasswordExpiration,
  securityPolicyWithInvalidBlockingDuration,
} from './defaults';
import { PasswordSecurityPolicyToAPI } from './models';

import LocalAuthentication from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('../logos/passwordPadlock.svg');

const cancelTokenRequestParam = { cancelToken: {} };

const cancelTokenPutParams = {
  ...cancelTokenRequestParam,
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
};

const renderAuthentication = (): RenderResult =>
  render(
    <TestQueryProvider>
      <LocalAuthentication />
    </TestQueryProvider>,
  );

const mockGetPasswordSecurityPolicy = (
  securityPolicy: PasswordSecurityPolicyToAPI,
): void => {
  mockedAxios.get.mockReset();
  mockedAxios.get.mockResolvedValue({
    data: securityPolicy,
  });
};

describe('Authentication', () => {
  beforeEach(() => {
    mockedAxios.put.mockReset();
    mockedAxios.put.mockResolvedValue({
      data: {},
    });
  });

  it('updates the retrieved form recommended values and send the data when the "Save" button is clicked', async () => {
    mockGetPasswordSecurityPolicy(defaultPasswordSecurityPolicyAPI);
    renderAuthentication();

    expect(
      screen.getByText(labelDefinePasswordPasswordSecurityPolicy),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Local),
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    });
    expect(screen.getByText(labelPasswordExpirationPolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();

    await waitFor(() => {
      expect(screen.getByText(labelSave)).toBeDisabled();
    });

    userEvent.type(
      screen.getByLabelText(labelMinimumPasswordLength),
      '{selectall}{backspace}45',
    );

    await waitFor(() => {
      expect(screen.getByText(labelSave)).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelSave));

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Local),
        {
          password_security_policy: {
            ...defaultPasswordSecurityPolicyAPI.password_security_policy,
            password_min_length: 45,
          },
        },
        cancelTokenPutParams,
      );
    });
  });

  it('updates the retrieved form recommended values and reset the form to the inital values', async () => {
    mockGetPasswordSecurityPolicy(defaultPasswordSecurityPolicyAPI);
    renderAuthentication();

    expect(
      screen.getByText(labelDefinePasswordPasswordSecurityPolicy),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Local),
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    });
    expect(screen.getByText(labelPasswordExpirationPolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();

    await waitFor(() => {
      expect(screen.getByText(labelReset)).toBeDisabled();
    });

    userEvent.type(
      screen.getByLabelText(labelMinimumPasswordLength),
      '{selectall}{backspace}45',
    );

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      '{selectall}{backspace}8',
    );

    await waitFor(() => {
      expect(screen.getByText(labelReset)).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelReset));

    await waitFor(() => {
      expect(screen.getByText(labelResetTheForm)).toBeInTheDocument();
    });

    expect(screen.getByText(labelDoYouWantToResetTheForm)).toBeInTheDocument();

    userEvent.click(screen.getAllByText(labelReset)[1]);

    await waitFor(() => {
      expect(screen.getByLabelText(labelMinimumPasswordLength)).toHaveValue(12);
    });
  });

  it('updates the retrieved form values and send the data when the "Save" button is clicked', async () => {
    mockGetPasswordSecurityPolicy(retrievedPasswordSecurityPolicyAPI);
    renderAuthentication();

    expect(
      screen.getByText(labelDefinePasswordPasswordSecurityPolicy),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Local),
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    });
    expect(screen.getByText(labelPasswordExpirationPolicy)).toBeInTheDocument();
    expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();

    await waitFor(() => {
      expect(screen.getByText(labelSave)).toBeDisabled();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      '{selectall}{backspace}2',
    );

    await waitFor(() => {
      expect(screen.getByText(labelSave)).not.toBeDisabled();
    });

    userEvent.click(screen.getByText(labelSave));

    await waitFor(() => {
      expect(mockedAxios.put).toHaveBeenCalledWith(
        authenticationProvidersEndpoint(Provider.Local),
        {
          password_security_policy: {
            ...retrievedPasswordSecurityPolicyAPI.password_security_policy,
            attempts: 2,
          },
        },
        cancelTokenPutParams,
      );
    });
  });
});

describe('Password case policy', () => {
  beforeEach(() => {
    mockGetPasswordSecurityPolicy(defaultPasswordSecurityPolicyAPI);
  });

  it('renders the password case policy fields with values', async () => {
    renderAuthentication();

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelMinimumPasswordLength),
      ).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    });

    expect(screen.getByLabelText(labelMinimumPasswordLength)).toHaveValue(12);
    expect(
      screen.getAllByLabelText(labelPasswordMustContainLowerCase)[0],
    ).toBeInTheDocument();
    expect(
      screen.getAllByLabelText(labelPasswordMustContainUpperCase)[0],
    ).toBeInTheDocument();
    expect(
      screen.getAllByLabelText(labelPasswordMustContainNumbers)[0],
    ).toBeInTheDocument();
    expect(
      screen.getAllByLabelText(labelPasswordMustContainSpecialCharacters)[0],
    ).toBeInTheDocument();
    expect(screen.getByText(labelStrong)).toBeInTheDocument();
  });

  it('changes the password minimum length value when "45" is typed in the input', async () => {
    renderAuthentication();

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelMinimumPasswordLength),
      ).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelMinimumPasswordLength),
      '{selectall}{backspace}45',
    );

    await waitFor(() => {
      expect(screen.getByLabelText(labelMinimumPasswordLength)).toHaveValue(45);
    });
  });

  it('displays the efficiency level according to the selected cases when cases button are clicked', async () => {
    mockGetPasswordSecurityPolicy(defaultPasswordSecurityPolicyWithNullValues);
    renderAuthentication();

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelMinimumPasswordLength),
      ).toBeInTheDocument();
    });

    userEvent.click(
      screen.getAllByLabelText(labelPasswordMustContainLowerCase)[0],
    );
    userEvent.click(
      screen.getAllByLabelText(labelPasswordMustContainUpperCase)[0],
    );
    userEvent.click(
      screen.getAllByLabelText(labelPasswordMustContainNumbers)[0],
    );
    userEvent.click(
      screen.getAllByLabelText(labelPasswordMustContainSpecialCharacters)[0],
    );

    await waitFor(() => {
      expect(screen.getByText(labelStrong)).toBeInTheDocument();
    });
    userEvent.click(
      screen.getAllByLabelText(labelPasswordMustContainSpecialCharacters)[0],
    );

    await waitFor(() => {
      expect(screen.getByText(labelGood)).toBeInTheDocument();
    });

    userEvent.click(
      screen.getAllByLabelText(labelPasswordMustContainNumbers)[0],
    );

    await waitFor(() => {
      expect(screen.getByText(labelWeak)).toBeInTheDocument();
    });
  });
});

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
    resetMocks();
    mockResponseOnce({ data: retrievedContacts });
    mockedAxios.get.mockReset();
    mockedAxios.get.mockResolvedValueOnce({
      data: defaultPasswordSecurityPolicyAPI,
    });
  });

  it('renders the password expiration policy fields with values', async () => {
    renderAuthentication();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(screen.getByText(labelPasswordExpiresAfter)).toBeInTheDocument();
    });

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
    renderAuthentication();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(screen.getByText(labelPasswordExpiresAfter)).toBeInTheDocument();
    });

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
    mockGetPasswordSecurityPolicy(
      securityPolicyWithInvalidDelayBeforeNewPassword,
    );
    renderAuthentication();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(
        screen.getByText(labelMinimumTimeBetweenPasswordChanges),
      ).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(
        screen.getByText(labelChooseADurationBetween1HourAnd1Week),
      ).toBeInTheDocument();
    });

    mockGetPasswordSecurityPolicy(securityPolicyWithInvalidPasswordExpiration);
    renderAuthentication();

    await waitFor(() => {
      expect(
        screen.getByText(labelChooseADurationBetween7DaysAnd12Months),
      ).toBeInTheDocument();
    });
  });

  it('does not display any error message when the delay before new password time is cleared', async () => {
    renderAuthentication();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(
        screen.getByText(labelMinimumTimeBetweenPasswordChanges),
      ).toBeInTheDocument();
    });

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
    renderAuthentication();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(
        screen.getByText(labelLast3PasswordsCanBeReused),
      ).toBeInTheDocument();
    });

    userEvent.click(screen.getByLabelText(labelLast3PasswordsCanBeReused));

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelLast3PasswordsCanBeReused),
      ).toBeChecked();
    });
  });

  it('updates the excluded users field when an user is selected from the retrieved options', async () => {
    renderAuthentication();

    await waitFor(() => {
      expect(
        screen.getByText(labelPasswordExpirationPolicy),
      ).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(screen.getByLabelText(labelExcludedUsers)).toBeInTheDocument();
    });

    userEvent.click(screen.getByLabelText(labelExcludedUsers));

    await waitFor(() => {
      expect(getFetchCall(0)).toEqual(
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

describe('Password Blocking Policy', () => {
  beforeEach(() => {
    mockGetPasswordSecurityPolicy(defaultPasswordSecurityPolicyAPI);
  });

  it('renders the password blocking policy fields with values', async () => {
    renderAuthentication();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      ).toBeInTheDocument();
    });

    expect(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
    ).toHaveValue(5);

    expect(
      screen.getByText(labelTimeThatMustPassBeforeNewConnection),
    ).toBeInTheDocument();

    expect(
      screen.getByLabelText(
        `${labelTimeThatMustPassBeforeNewConnection} ${labelMinutes}`,
      ),
    ).toHaveTextContent('15');

    expect(screen.getByText(labelWeak)).toBeInTheDocument();
  });

  it('displays an error message when the number of attempts is outside the bounds', async () => {
    renderAuthentication();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      ).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      '0',
    );

    await waitFor(() => {
      expect(
        screen.getByText(labelChooseAValueBetween1and10),
      ).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      '{selectall}{backspace}8',
    );

    await waitFor(() => {
      expect(
        screen.queryByText(labelChooseAValueBetween1and10),
      ).not.toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      '11',
    );

    await waitFor(() => {
      expect(
        screen.getByText(labelChooseAValueBetween1and10),
      ).toBeInTheDocument();
    });
  });

  it('displays an error message in the "Time blocking duration" field when the number of attempts is cleared', async () => {
    renderAuthentication();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      ).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(
        screen.getByText(
          labelThisWillNotBeUsedBecauseNumberOfAttemptsIsNotDefined,
        ),
      ).toBeInTheDocument();
    });
  });

  it('displays an error message when the time blocking duration is 7 days and 1 hour', async () => {
    mockGetPasswordSecurityPolicy(securityPolicyWithInvalidBlockingDuration);
    renderAuthentication();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(
        screen.getByText(labelBlockingDurationMustBeLessThanOrEqualTo7Days),
      ).toBeInTheDocument();
    });
  });

  it('displays the efficiency level when the number of attempts changes', async () => {
    renderAuthentication();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(
        screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      ).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      '{selectall}{backspace}2',
    );

    await waitFor(() => {
      expect(screen.getAllByText(labelStrong)).toHaveLength(2);
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeUserIsBlocked),
      '{selectall}{backspace}4',
    );

    await waitFor(() => {
      expect(screen.getByText(labelGood)).toBeInTheDocument();
    });
  });

  it('displays the efficiency level when the time blocking duration changes', async () => {
    renderAuthentication();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    await waitFor(() => {
      expect(
        screen.getByLabelText(
          `${labelTimeThatMustPassBeforeNewConnection} ${labelDay}`,
        ),
      ).toBeInTheDocument();
    });

    userEvent.click(
      screen.getByLabelText(
        `${labelTimeThatMustPassBeforeNewConnection} ${labelDay}`,
      ),
    );
    userEvent.click(screen.getByText('6'));

    await waitFor(() => {
      expect(screen.getAllByText(labelStrong)).toHaveLength(2);
    });

    userEvent.click(
      screen.getByLabelText(
        `${labelTimeThatMustPassBeforeNewConnection} ${labelDays}`,
      ),
    );
    userEvent.click(screen.getByText('3'));

    userEvent.click(
      screen.getByLabelText(
        `${labelTimeThatMustPassBeforeNewConnection} ${labelMinutes}`,
      ),
    );
    userEvent.click(screen.getAllByText('0')[1]);

    await waitFor(() => {
      expect(screen.getAllByText(labelGood)).toHaveLength(2);
    });
  });
});
