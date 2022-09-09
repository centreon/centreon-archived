import userEvent from '@testing-library/user-event';
import { Formik } from 'formik';

import { render, RenderResult, screen, waitFor } from '@centreon/ui';

import {
  PasswordSecurityPolicy,
  PasswordSecurityPolicyFromAPI,
} from '../../models';
import useValidationSchema from '../../useValidationSchema';
import {
  labelPasswordMustContainLowerCase,
  labelPasswordMustContainNumbers,
  labelPasswordMustContainSpecialCharacters,
  labelPasswordMustContainUpperCase,
  labelGood,
  labelMaximum128Characters,
  labelMinimum8Characters,
  labelPasswordCasePolicy,
  labelMinimumPasswordLength,
  labelRequired,
  labelStrong,
  labelWeak,
} from '../../translatedLabels';
import {
  defaultPasswordSecurityPolicy,
  defaultPasswordSecurityPolicyWithNullValues,
} from '../defaults';

import PasswordCasePolicy from '.';

const noOp = jest.fn();

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
      {(): JSX.Element => <PasswordCasePolicy />}
    </Formik>
  );
};

const renderPasswordCasePolicy = (
  initialValues: PasswordSecurityPolicyFromAPI = defaultPasswordSecurityPolicy,
): RenderResult =>
  render(
    <TestComponent initialValues={initialValues.password_security_policy} />,
  );

describe('Password case policy', () => {
  it('renders the password case policy fields with values', async () => {
    renderPasswordCasePolicy();

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
    renderPasswordCasePolicy();

    userEvent.type(
      screen.getByLabelText(labelMinimumPasswordLength),
      '{selectall}{backspace}45',
    );

    await waitFor(() => {
      expect(screen.getByLabelText(labelMinimumPasswordLength)).toHaveValue(45);
    });
  });

  it('displays an error message when password minimum length input is outside the bounds', async () => {
    renderPasswordCasePolicy();

    userEvent.type(
      screen.getByLabelText(labelMinimumPasswordLength),
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(screen.getByText(labelRequired)).toBeInTheDocument();
    });

    userEvent.type(screen.getByLabelText(labelMinimumPasswordLength), '7');

    await waitFor(() => {
      expect(screen.getByText(labelMinimum8Characters)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelMinimumPasswordLength),
      '{selectall}{backspace}129',
    );

    await waitFor(() => {
      expect(screen.getByText(labelMaximum128Characters)).toBeInTheDocument();
    });
  });

  it('displays the efficiency level according to the selected cases when cases button are clicked', async () => {
    renderPasswordCasePolicy(defaultPasswordSecurityPolicyWithNullValues);

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
