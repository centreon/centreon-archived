import { MouseEvent, useEffect, useState } from 'react';

import dayjs from 'dayjs';
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter';
import { and, cond, equals } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';

import { FormHelperText, Typography, Button, Popover } from '@mui/material';
import { LocalizationProvider } from '@mui/lab';
import makeStyles from '@mui/styles/makeStyles';
import AccessTimeIcon from '@mui/icons-material/AccessTime';

import { dateTimeFormat, useLocaleDateTimeFormat } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import {
  labelEndDate,
  labelStartDate,
  labelEndDateGreaterThanStartDate,
  labelTo,
  labelCompactTimePeriod,
  labelFrom,
} from '../../../translatedLabels';
import {
  CustomTimePeriod,
  CustomTimePeriodProperty,
} from '../../../Details/tabs/Graph/models';
import useDateTimePickerAdapter from '../../../useDateTimePickerAdapter';

import DateTimePickerInput from './DateTimePickerInput';

interface AcceptDateProps {
  date: Date;
  property: CustomTimePeriodProperty;
}

interface Props {
  acceptDate: (props: AcceptDateProps) => void;
  customTimePeriod: CustomTimePeriod;
  isCompact: boolean;
}

dayjs.extend(isSameOrAfter);

const useStyles = makeStyles((theme) => ({
  button: {
    padding: theme.spacing(0, 0.5),
  },
  buttonContent: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
    display: 'grid',
    gridTemplateColumns: 'min-content auto',
  },
  compactFromTo: {
    columnGap: theme.spacing(0.5),
    display: 'grid',
    grid: 'repeat(2, min-content) / min-content auto',
  },
  error: {
    textAlign: 'center',
  },
  fromTo: {
    alignItems: 'center',
    columnGap: theme.spacing(0.5),
    display: 'grid',
    gridTemplateColumns: 'repeat(4, auto)',
  },
  minimalFromTo: {
    display: 'grid',
    gridTemplateRows: 'repeat(2, min-content)',
    rowGap: theme.spacing(0.3),
  },
  minimalPickers: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
    display: 'grid',
    gridTemplateColumns: 'min-content auto',
  },
  pickerText: {
    cursor: 'pointer',
    lineHeight: '1.2',
  },
  pickers: {
    alignItems: 'center',
    columnGap: theme.spacing(0.5),
    display: 'grid',
    gridTemplateColumns: `minmax(${theme.spacing(15)}, ${theme.spacing(
      17,
    )}px) min-content minmax(${theme.spacing(15)}, ${theme.spacing(17)})`,
  },
  popover: {
    display: 'grid',
    gridTemplateRows: 'auto auto auto',
    justifyItems: 'center',
    padding: theme.spacing(1, 2),
    rowGap: theme.spacing(1),
  },
}));

const CustomTimePeriodPickers = ({
  customTimePeriod,
  acceptDate,
  isCompact: isMinimalWidth,
}: Props): JSX.Element => {
  const classes = useStyles(isMinimalWidth);
  const { t } = useTranslation();
  const [anchorEl, setAnchorEl] = useState<Element | null>(null);
  const [start, setStart] = useState<Date>(customTimePeriod.start);
  const [end, setEnd] = useState<Date>(customTimePeriod.end);
  const { format } = useLocaleDateTimeFormat();
  const { locale } = useAtomValue(userAtom);
  const { Adapter } = useDateTimePickerAdapter();

  const isInvalidDate = ({ startDate, endDate }): boolean =>
    dayjs(startDate).isSameOrAfter(dayjs(endDate), 'minute');

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
    acceptDate({
      date,
      property,
    });
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

  const openPopover = (event: MouseEvent): void => {
    setAnchorEl(event.currentTarget);
  };

  const closePopover = (): void => {
    setAnchorEl(null);
  };

  const displayPopover = Boolean(anchorEl);

  const error = isInvalidDate({ endDate: end, startDate: start });

  return (
    <>
      <Button
        aria-label={t(labelCompactTimePeriod)}
        className={classes.button}
        color="primary"
        data-testid={labelCompactTimePeriod}
        variant="outlined"
        onClick={openPopover}
      >
        <div className={classes.buttonContent}>
          <AccessTimeIcon />
          <div
            className={isMinimalWidth ? classes.compactFromTo : classes.fromTo}
          >
            <Typography variant="caption">{t(labelFrom)}:</Typography>
            <Typography variant="caption">
              {format({
                date: customTimePeriod.start,
                formatString: dateTimeFormat,
              })}
            </Typography>
            <Typography variant="caption">{t(labelTo)}:</Typography>
            <Typography variant="caption">
              {format({
                date: customTimePeriod.end,
                formatString: dateTimeFormat,
              })}
            </Typography>
          </div>
        </div>
      </Button>
      <Popover
        anchorEl={anchorEl}
        anchorOrigin={{
          horizontal: 'center',
          vertical: 'top',
        }}
        open={displayPopover}
        transformOrigin={{
          horizontal: 'center',
          vertical: 'top',
        }}
        onClose={closePopover}
      >
        <LocalizationProvider
          dateAdapter={Adapter}
          locale={locale.substring(0, 2)}
        >
          <div className={classes.popover}>
            <div>
              <Typography>{t(labelFrom)}</Typography>
              <div aria-label={t(labelStartDate)}>
                <DateTimePickerInput
                  changeDate={changeDate}
                  date={start}
                  maxDate={customTimePeriod.end}
                  property={CustomTimePeriodProperty.start}
                  setDate={setStart}
                />
              </div>
            </div>
            <div>
              <Typography>{t(labelTo)}</Typography>
              <div aria-label={t(labelEndDate)}>
                <DateTimePickerInput
                  changeDate={changeDate}
                  date={end}
                  minDate={customTimePeriod.start}
                  property={CustomTimePeriodProperty.end}
                  setDate={setEnd}
                />
              </div>
            </div>

            {error && (
              <FormHelperText error className={classes.error}>
                {t(labelEndDateGreaterThanStartDate)}
              </FormHelperText>
            )}
          </div>
        </LocalizationProvider>
      </Popover>
    </>
  );
};

export default CustomTimePeriodPickers;
