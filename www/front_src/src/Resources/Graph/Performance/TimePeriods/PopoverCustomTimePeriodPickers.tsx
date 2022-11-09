import { useEffect, useState } from 'react';

import { userAtom } from 'centreon-frontend/packages/ui-context/src';
import dayjs from 'dayjs';
import { useAtomValue } from 'jotai/utils';
import { and, cond, equals } from 'ramda';
import { useTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';

import { LocalizationProvider } from '@mui/lab';
import {
  FormHelperText,
  Popover,
  PopoverOrigin,
  PopoverReference,
  Typography,
} from '@mui/material';

import {
  CustomTimePeriod,
  CustomTimePeriodProperty,
} from '../../../Details/tabs/Graph/models';
import {
  labelEndDate,
  labelEndDateGreaterThanStartDate,
  labelFrom,
  labelStartDate,
  labelTo,
} from '../../../translatedLabels';
import useDateTimePickerAdapter from '../../../useDateTimePickerAdapter';

import DateTimePickerInput from './DateTimePickerInput';
import { AnchorReference } from './models';

const useStyles = makeStyles()((theme) => ({
  error: {
    textAlign: 'center',
  },
  paper: {
    '& .MuiPopover-paper': {
      minWidth: 250,
    },
  },
  popover: {
    display: 'flex',
    flexDirection: 'column',
    gap: theme.spacing(1),
    justifyItems: 'center',
    padding: theme.spacing(1, 2),
  },
}));

interface AcceptDateProps {
  date: Date;
  property: CustomTimePeriodProperty;
}

interface Props {
  acceptDate: (props: AcceptDateProps) => void;
  anchorOrigin?: PopoverOrigin;
  anchorReference?: PopoverReference;
  classNameError?: string;
  classNamePaper?: string;
  classNamePicker?: string;
  customTimePeriod: CustomTimePeriod;
  getIsErrorDatePicker?: (value: boolean) => void;
  maxDatePickerEndInput?: Date | dayjs.Dayjs;
  maxDatePickerStartInput?: Date;
  minDatePickerEndInput?: Date;
  minDatePickerStartInput?: Date;
  onClose?: () => void;
  open: boolean;
  pickerWithoutInitialValue?: boolean;
  reference?: AnchorReference;
  renderBody?: JSX.Element;
  renderFooter?: JSX.Element;
  renderTitle?: JSX.Element;
  transformOrigin?: PopoverOrigin;
  waitToSelectMinutes?: boolean;
}

interface CallbackForSelectMinutes {
  date: Date;
  property: CustomTimePeriodProperty;
}

const PopoverCustomTimePeriodPickers = ({
  reference,
  anchorReference = 'none',
  anchorOrigin = {
    horizontal: 'center',
    vertical: 'top',
  },
  transformOrigin = {
    horizontal: 'center',
    vertical: 'top',
  },
  open,
  onClose,
  classNamePaper,
  classNamePicker,
  customTimePeriod,
  acceptDate,
  renderTitle,
  renderBody,
  renderFooter,
  pickerWithoutInitialValue,
  maxDatePickerStartInput = customTimePeriod?.end,
  minDatePickerStartInput,
  minDatePickerEndInput = customTimePeriod?.start,
  maxDatePickerEndInput,
  waitToSelectMinutes = false,
  classNameError,
  getIsErrorDatePicker,
}: Props): JSX.Element => {
  const { classes, cx } = useStyles();
  const { t } = useTranslation();
  const [start, setStart] = useState<Date>(customTimePeriod.start);
  const [end, setEnd] = useState<Date>(customTimePeriod.end);
  const [viewStartPicker, setViewStartPicker] = useState<string | null>(null);
  const [viewEndPicker, setViewEndPicker] = useState<string | null>(null);

  const { locale } = useAtomValue(userAtom);
  const { Adapter } = useDateTimePickerAdapter();

  const isInvalidDate = ({ startDate, endDate }): boolean =>
    dayjs(startDate).isSameOrAfter(dayjs(endDate), 'minute');

  const error = isInvalidDate({ endDate: end, startDate: start });

  getIsErrorDatePicker?.(error);

  const changeDate = ({ property, date }): void => {
    const currentDate = customTimePeriod[property];

    cond([
      [
        (): boolean => equals(CustomTimePeriodProperty.start, property),
        (): void => setStart(date),
      ],
      [
        (): boolean => equals(CustomTimePeriodProperty.end, property),
        (): void => setEnd(date),
      ],
    ])();

    if (
      dayjs(date).isSame(dayjs(currentDate)) ||
      isInvalidDate({ endDate: end, startDate: start }) ||
      !dayjs(date).isValid()
    ) {
      return;
    }

    callbackForSelectMinutes({
      date,
      property,
    });
  };

  const callbackForSelectMinutes = ({
    property,
    date,
  }: CallbackForSelectMinutes): void => {
    if (!waitToSelectMinutes) {
      acceptDate({
        date,
        property,
      });

      return;
    }

    if (
      (!equals(viewStartPicker, 'minutes') && equals(property, 'start')) ||
      (!equals(viewEndPicker, 'minutes') && equals(property, 'end'))
    ) {
      return;
    }
    acceptDate({
      date,
      property,
    });
    if (equals(viewStartPicker, 'minutes') && equals(property, 'start')) {
      setViewStartPicker(null);
    }
    if (equals(viewEndPicker, 'minutes') && equals(property, 'end')) {
      setViewEndPicker(null);
    }
  };

  useEffect(() => {
    if (
      and(
        dayjs(customTimePeriod.start).isSame(dayjs(start), 'minute'),
        dayjs(customTimePeriod.end).isSame(dayjs(end), 'minute'),
      )
    ) {
      return;
    }
    setStart(customTimePeriod.start);
    setEnd(customTimePeriod.end);
  }, [customTimePeriod.start, customTimePeriod.end]);

  const viewChangeStartPicker = (data: string): void => {
    setViewStartPicker(data);
  };
  const viewChangeEndPicker = (data: string): void => {
    setViewEndPicker(data);
  };

  return (
    <div>
      <Popover
        anchorEl={reference?.anchorEl}
        anchorOrigin={anchorOrigin}
        anchorPosition={reference?.anchorPosition}
        anchorReference={anchorReference}
        className={cx(classes.paper, classNamePaper)}
        open={open}
        transformOrigin={transformOrigin}
        onClose={onClose}
      >
        {renderTitle}
        <LocalizationProvider
          dateAdapter={Adapter}
          locale={locale.substring(0, 2)}
        >
          <div className={cx(classes.popover, classNamePicker)}>
            <div>
              <Typography>{t(labelFrom)}</Typography>
              <div aria-label={t(labelStartDate)}>
                <DateTimePickerInput
                  changeDate={changeDate}
                  date={start}
                  maxDate={maxDatePickerStartInput}
                  minDate={minDatePickerStartInput}
                  property={CustomTimePeriodProperty.start}
                  setDate={setStart}
                  withoutInitialValue={pickerWithoutInitialValue}
                  onViewChange={viewChangeStartPicker}
                />
              </div>
            </div>
            <div>
              <Typography>{t(labelTo)}</Typography>
              <div aria-label={t(labelEndDate)}>
                <DateTimePickerInput
                  changeDate={changeDate}
                  date={end}
                  maxDate={maxDatePickerEndInput}
                  minDate={minDatePickerEndInput}
                  property={CustomTimePeriodProperty.end}
                  setDate={setEnd}
                  withoutInitialValue={pickerWithoutInitialValue}
                  onViewChange={viewChangeEndPicker}
                />
              </div>
            </div>
          </div>
          {error && (
            <FormHelperText error className={cx(classes.error, classNameError)}>
              {t(labelEndDateGreaterThanStartDate)}
            </FormHelperText>
          )}
        </LocalizationProvider>
        {renderBody}
        {renderFooter}
      </Popover>
    </div>
  );
};

export default PopoverCustomTimePeriodPickers;
