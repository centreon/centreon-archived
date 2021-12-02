import * as React from 'react';

import { render, RenderResult, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Formik } from 'formik';

import { SecurityPolicy } from '../../models';
import useValidationSchema from '../../useValidationSchema';
import {
  labelForceToUseLowerCase,
  labelForceToUseNumbers,
  labelForceToUseSpecialCharacters,
  labelForceToUseUpperCase,
  labelGood,
  labelMaximum128Characters,
  labelMinimum8Characters,
  labelPasswordCasePolicy,
  labelPasswordLength,
  labelRequired,
  labelStrong,
  labelWeak,
} from '../../translatedLabels';
import {
  defaultSecurityPolicy,
  defaultSecurityPolicyWithNullValues,
} from '../defaults';

import PasswordCasePolicy from '.';

const noOp = jest.fn();

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
      {(): JSX.Element => <PasswordCasePolicy />}
    </Formik>
  );
};

const renderPasswordCasePolicy = (
  initialValues: SecurityPolicy = defaultSecurityPolicy,
): RenderResult => render(<TestComponent initialValues={initialValues} />);

describe('Password case policy', () => {
  it('renders the password case policy fields with values', async () => {
    renderPasswordCasePolicy();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordCasePolicy)).toBeInTheDocument();
    });

    expect(screen.getByLabelText(labelPasswordLength)).toHaveValue(12);
    expect(screen.getByLabelText(labelForceToUseLowerCase)).toBeInTheDocument();
    expect(screen.getByLabelText(labelForceToUseUpperCase)).toBeInTheDocument();
    expect(screen.getByLabelText(labelForceToUseNumbers)).toBeInTheDocument();
    expect(
      screen.getByLabelText(labelForceToUseSpecialCharacters),
    ).toBeInTheDocument();
    expect(screen.getByText(labelStrong)).toBeInTheDocument();
  });

  it('changes the password minimum length value when "45" is typed in the input', async () => {
    renderPasswordCasePolicy();

    userEvent.type(
      screen.getByLabelText(labelPasswordLength),
      '{selectall}{backspace}45',
    );

    await waitFor(() => {
      expect(screen.getByLabelText(labelPasswordLength)).toHaveValue(45);
    });
  });
  it('displays an error message when password minimum length input is emptied', async () => {
    renderPasswordCasePolicy();

    userEvent.type(
      screen.getByLabelText(labelPasswordLength),
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(screen.getByText(labelRequired)).toBeInTheDocument();
    });
  });

  it('displays an error message when "7" is typed in the password minimum length input', async () => {
    renderPasswordCasePolicy();

    userEvent.type(
      screen.getByLabelText(labelPasswordLength),
      '{selectall}{backspace}7',
    );

    await waitFor(() => {
      expect(screen.getByText(labelMinimum8Characters)).toBeInTheDocument();
    });
  });

  it('displays an error message when "129" is typed in the password minimum length input', async () => {
    renderPasswordCasePolicy();

    userEvent.type(
      screen.getByLabelText(labelPasswordLength),
      '{selectall}{backspace}129',
    );

    await waitFor(() => {
      expect(screen.getByText(labelMaximum128Characters)).toBeInTheDocument();
    });
  });

  it('displays "Strong" when all cases buttons are selected', async () => {
    renderPasswordCasePolicy(defaultSecurityPolicyWithNullValues);

    userEvent.click(screen.getByLabelText(labelForceToUseLowerCase));
    userEvent.click(screen.getByLabelText(labelForceToUseUpperCase));
    userEvent.click(screen.getByLabelText(labelForceToUseNumbers));
    userEvent.click(screen.getByLabelText(labelForceToUseSpecialCharacters));

    await waitFor(() => {
      expect(screen.getByText(labelStrong)).toBeInTheDocument();
    });
  });

  it('displays "Good" when only 3 cases buttons are selected', async () => {
    renderPasswordCasePolicy(defaultSecurityPolicyWithNullValues);

    userEvent.click(screen.getByLabelText(labelForceToUseLowerCase));
    userEvent.click(screen.getByLabelText(labelForceToUseUpperCase));
    userEvent.click(screen.getByLabelText(labelForceToUseSpecialCharacters));

    await waitFor(() => {
      expect(screen.getByText(labelGood)).toBeInTheDocument();
    });
  });

  it('displays "Weak" when only 2 cases buttons are selected', async () => {
    renderPasswordCasePolicy(defaultSecurityPolicyWithNullValues);

    userEvent.click(screen.getByLabelText(labelForceToUseNumbers));
    userEvent.click(screen.getByLabelText(labelForceToUseSpecialCharacters));

    await waitFor(() => {
      expect(screen.getByText(labelWeak)).toBeInTheDocument();
    });
  });
});
