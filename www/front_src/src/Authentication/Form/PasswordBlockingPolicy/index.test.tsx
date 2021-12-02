import * as React from 'react';

import { render, RenderResult, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Formik } from 'formik';

import { SecurityPolicy } from '../../models';
import useValidationSchema from '../../useValidationSchema';
import { defaultSecurityPolicy } from '../defaults';
import {
  labelBlockingDurationMustBeLessOrEqualThan7Days,
  labelBlockingTimeBeforeNewConnectionAttempt,
  labelChooseAValueBetween1and10,
  labelDay,
  labelGood,
  labelMinutes,
  labelNumberOfAttemptsBeforeBlockNewAttempt,
  labelPasswordBlockingPolicy,
  labelSecond,
  labelStrong,
  labelThisWillNotBeUsedBecauseNumberOfAttemptsIsNotDefined,
  labelWeak,
} from '../../translatedLabels';

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

const renderPasswordBlockingPolicy = (
  initialValues: SecurityPolicy = defaultSecurityPolicy,
): RenderResult => render(<TestComponent initialValues={initialValues} />);

describe('Password Blocking Policy', () => {
  it('renders the password blocking policy fields with values', async () => {
    renderPasswordBlockingPolicy();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    expect(
      screen.getByLabelText(labelNumberOfAttemptsBeforeBlockNewAttempt),
    ).toHaveValue(5);

    expect(
      screen.getByText(labelBlockingTimeBeforeNewConnectionAttempt),
    ).toBeInTheDocument();

    expect(
      screen.getByLabelText(
        `${labelBlockingTimeBeforeNewConnectionAttempt} ${labelMinutes}`,
      ),
    ).toHaveValue(15);

    expect(screen.getAllByText(labelWeak)).toHaveLength(2);
  });

  it('displays an error message when the number of attempts is 0', async () => {
    renderPasswordBlockingPolicy();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeBlockNewAttempt),
      '0',
    );

    await waitFor(() => {
      expect(
        screen.getByText(labelChooseAValueBetween1and10),
      ).toBeInTheDocument();
    });
  });

  it('displays an error message when the number of attempts is 11', async () => {
    renderPasswordBlockingPolicy();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeBlockNewAttempt),
      '11',
    );

    await waitFor(() => {
      expect(
        screen.getByText(labelChooseAValueBetween1and10),
      ).toBeInTheDocument();
    });
  });

  it('displays an error message in the "Time blocking duration" field when the number of attempts is emptied', async () => {
    renderPasswordBlockingPolicy();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeBlockNewAttempt),
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

  it('displays an error message when the time blocking duration is 7 days and 1 second', async () => {
    renderPasswordBlockingPolicy();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(
        `${labelBlockingTimeBeforeNewConnectionAttempt} ${labelMinutes}`,
      ),
      '{selectall}{backspace}',
    );

    userEvent.type(
      screen.getByLabelText(
        `${labelBlockingTimeBeforeNewConnectionAttempt} ${labelDay}`,
      ),
      '7',
    );

    userEvent.type(
      screen.getByLabelText(
        `${labelBlockingTimeBeforeNewConnectionAttempt} ${labelSecond}`,
      ),
      '1',
    );

    await waitFor(() => {
      expect(
        screen.getByText(labelBlockingDurationMustBeLessOrEqualThan7Days),
      ).toBeInTheDocument();
    });
  });

  it('displays "Strong" when the number of attempts is 8', async () => {
    renderPasswordBlockingPolicy();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeBlockNewAttempt),
      '{selectall}{backspace}8',
    );

    await waitFor(() => {
      expect(screen.getByText(labelStrong)).toBeInTheDocument();
    });
  });

  it('displays "Good" when the number of attempts is 6', async () => {
    renderPasswordBlockingPolicy();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(labelNumberOfAttemptsBeforeBlockNewAttempt),
      '{selectall}{backspace}6',
    );

    await waitFor(() => {
      expect(screen.getByText(labelGood)).toBeInTheDocument();
    });
  });

  it('displays "Strong" when the time blocking duration is 6 days', async () => {
    renderPasswordBlockingPolicy();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(
        `${labelBlockingTimeBeforeNewConnectionAttempt} ${labelDay}`,
      ),
      '{selectall}{backspace}6',
    );

    userEvent.type(
      screen.getByLabelText(
        `${labelBlockingTimeBeforeNewConnectionAttempt} ${labelMinutes}`,
      ),
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(screen.getByText(labelStrong)).toBeInTheDocument();
    });
  });

  it('displays "Good" when the time blocking duration is 3 days', async () => {
    renderPasswordBlockingPolicy();

    await waitFor(() => {
      expect(screen.getByText(labelPasswordBlockingPolicy)).toBeInTheDocument();
    });

    userEvent.type(
      screen.getByLabelText(
        `${labelBlockingTimeBeforeNewConnectionAttempt} ${labelDay}`,
      ),
      '{selectall}{backspace}3',
    );

    userEvent.type(
      screen.getByLabelText(
        `${labelBlockingTimeBeforeNewConnectionAttempt} ${labelMinutes}`,
      ),
      '{selectall}{backspace}',
    );

    await waitFor(() => {
      expect(screen.getByText(labelGood)).toBeInTheDocument();
    });
  });
});
