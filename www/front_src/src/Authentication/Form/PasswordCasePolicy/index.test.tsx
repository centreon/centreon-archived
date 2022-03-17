import * as React from 'react';

import userEvent from '@testing-library/user-event';
import { Formik } from 'formik';

import { render, RenderResult, screen, waitFor } from '@centreon/ui';

import { SecurityPolicy, SecurityPolicyFromAPI } from '../../models';
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
  initialValues: SecurityPolicyFromAPI = defaultSecurityPolicy,
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

    expect(screen.getByLabelText(labelPasswordLength)).toHaveValue(12);
    expect(
      screen.getAllByLabelText(labelForceToUseLowerCase)[0],
    ).toBeInTheDocument();
    expect(
      screen.getAllByLabelText(labelForceToUseUpperCase)[0],
    ).toBeInTheDocument();
    expect(
      screen.getAllByLabelText(labelForceToUseNumbers)[0],
    ).toBeInTheDocument();
    expect(
      screen.getAllByLabelText(labelForceToUseSpecialCharacters)[0],
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

  it('displays an error message when password minimum length input is outside the bounds', async () => {
    renderPasswordCasePolicy();

    userEvent.type(
      screen.getByLabelText(labelPasswordLength),
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(screen.getByText(labelRequired)).toBeInTheDocument();
    });

    userEvent.type(screen.getByLabelText(labelPasswordLength), '7');

    await waitFor(() => {
      expect(screen.getByText(labelMinimum8Characters)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelPasswordLength),
      '{selectall}{backspace}129',
    );

    await waitFor(() => {
      expect(screen.getByText(labelMaximum128Characters)).toBeInTheDocument();
    });
  });

  it('displays the efficiency level according to the selected cases when cases button are clicked', async () => {
    renderPasswordCasePolicy(defaultSecurityPolicyWithNullValues);

    userEvent.click(screen.getAllByLabelText(labelForceToUseLowerCase)[0]);
    userEvent.click(screen.getAllByLabelText(labelForceToUseUpperCase)[0]);
    userEvent.click(screen.getAllByLabelText(labelForceToUseNumbers)[0]);
    userEvent.click(
      screen.getAllByLabelText(labelForceToUseSpecialCharacters)[0],
    );

    await waitFor(() => {
      expect(screen.getByText(labelStrong)).toBeInTheDocument();
    });
    userEvent.click(
      screen.getAllByLabelText(labelForceToUseSpecialCharacters)[0],
    );

    await waitFor(() => {
      expect(screen.getByText(labelGood)).toBeInTheDocument();
    });

    userEvent.click(screen.getAllByLabelText(labelForceToUseNumbers)[0]);

    await waitFor(() => {
      expect(screen.getByText(labelWeak)).toBeInTheDocument();
    });
  });
});
