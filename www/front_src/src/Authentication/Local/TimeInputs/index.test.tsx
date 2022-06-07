import userEvent from '@testing-library/user-event';

import { render, RenderResult, screen } from '@centreon/ui';

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

    userEvent.click(screen.getByLabelText(`input ${labelMinute}`));
    userEvent.click(screen.getByText('34'));

    expect(mockChange).toHaveBeenCalledWith(2040000);
  });

  it('does not display options below the configured min value except 0', () => {
    renderTimeInput({
      inputLabel: 'input',
      labels: { plural: labelMinutes, singular: labelMinute },
      minOption: 2,
      name: 'input',
      onChange: mockChange,
      timeValue: 0,
      unit: 'minutes',
    });

    userEvent.click(screen.getByLabelText(`input ${labelMinute}`));

    expect(screen.getAllByText('0')[0]).toBeInTheDocument();
    expect(screen.queryByText('1')).not.toBeInTheDocument();
    expect(screen.getByText('2')).toBeInTheDocument();
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
