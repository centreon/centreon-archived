import * as React from 'react';

import { render, RenderResult, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import { labelMinute, labelMinutes } from '../translatedLabels';

import TimeInput, { TimeInputProps } from './TimeInput';

const mockChange = jest.fn();

const renderTimeInput = (props: TimeInputProps): RenderResult =>
  render(<TimeInput {...props} />);

describe('Time input', () => {
  it('updates the time value to 2040000 milliseconds value when "34" is typed in the input', () => {
    renderTimeInput({
      inputLabel: 'input',
      labels: { plural: labelMinutes, singular: labelMinute },
      name: 'input',
      onChange: mockChange,
      timeValue: 0,
      unit: 'minutes',
    });

    userEvent.type(screen.getByLabelText(`input ${labelMinute}`), '34');

    expect(mockChange).toHaveBeenCalledWith(2040000);
  });

  it('updates the time value to 0 milliseconds value when input is emptied', () => {
    renderTimeInput({
      inputLabel: 'input',
      labels: { plural: labelMinutes, singular: labelMinute },
      name: 'input',
      onChange: mockChange,
      timeValue: 1200000,
      unit: 'minutes',
    });

    userEvent.type(
      screen.getByLabelText(`input ${labelMinutes}`),
      '{selectall}{backspace}',
    );

    expect(mockChange).toHaveBeenCalledWith(0);
  });

  it('displays the label text in singular when the input value is 0', () => {
    renderTimeInput({
      inputLabel: 'input',
      labels: { plural: labelMinutes, singular: labelMinute },
      name: 'input',
      onChange: mockChange,
      timeValue: 0,
      unit: 'minutes',
    });

    expect(screen.getByText(labelMinute)).toBeInTheDocument();
  });

  it('displays the label text in singular when the input value is 1', () => {
    renderTimeInput({
      inputLabel: 'input',
      labels: { plural: labelMinutes, singular: labelMinute },
      name: 'input',
      onChange: mockChange,
      timeValue: 60000,
      unit: 'minutes',
    });

    expect(screen.getByText(labelMinute)).toBeInTheDocument();
  });

  it('displays the label text in plural when the input value is 2', () => {
    renderTimeInput({
      inputLabel: 'input',
      labels: { plural: labelMinutes, singular: labelMinute },
      name: 'input',
      onChange: mockChange,
      timeValue: 120000,
      unit: 'minutes',
    });

    expect(screen.getByText(labelMinutes)).toBeInTheDocument();
  });
});
