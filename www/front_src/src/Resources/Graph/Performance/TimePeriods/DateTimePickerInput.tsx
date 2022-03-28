import * as React from 'react';

import dayjs from 'dayjs';

import { DateTimePicker } from '@mui/lab';
import { TextFieldProps } from '@mui/material';

import { TextField } from '@centreon/ui';

import { CustomTimePeriodProperty } from '../../../Details/tabs/Graph/models';
import useDateTimePickerAdapter from '../../../useDateTimePickerAdapter';

interface Props {
  changeDate: (props) => void;
  date: Date;
  maxDate?: Date;
  minDate?: Date;
  property: CustomTimePeriodProperty;
  setDate: React.Dispatch<React.SetStateAction<Date>>;
}

const renderDateTimePickerTextField =
  (blur: () => void) =>
  ({ inputRef, inputProps, InputProps }: TextFieldProps): JSX.Element => {
    return (
      <TextField
        // eslint-disable-next-line react/no-unstable-nested-components
        EndAdornment={(): JSX.Element => <div>{InputProps?.endAdornment}</div>}
        inputProps={{
          ...inputProps,
          ref: inputRef,
          style: { padding: 8 },
        }}
        onBlur={blur}
      />
    );
  };

const DateTimePickerInput = ({
  date,
  maxDate,
  minDate,
  property,
  changeDate,
  setDate,
}: Props): JSX.Element => {
  const [isOpen, setIsOpen] = React.useState(false);
  const { getLocalAndConfiguredTimezoneOffset, formatKeyboardValue } =
    useDateTimePickerAdapter();

  const changeTime = (
    newValue: dayjs.Dayjs | null,
    keyBoardValue: string | undefined,
  ): void => {
    if (isOpen) {
      changeDate({ date: dayjs(newValue).toDate(), property });

      return;
    }
    const value = dayjs(formatKeyboardValue(keyBoardValue))
      .add(dayjs.duration({ hours: getLocalAndConfiguredTimezoneOffset({}) }))
      .toDate();

    setDate(value);
  };

  const blur = (): void => {
    changeDate({ date, property });
  };

  return (
    <DateTimePicker<dayjs.Dayjs>
      hideTabs
      PopperProps={{
        open: isOpen,
      }}
      maxDate={maxDate && dayjs(maxDate)}
      minDate={minDate && dayjs(minDate)}
      open={isOpen}
      renderInput={renderDateTimePickerTextField(blur)}
      value={date}
      onChange={changeTime}
      onClose={(): void => setIsOpen(false)}
      onOpen={(): void => setIsOpen(true)}
    />
  );
};

export default DateTimePickerInput;
