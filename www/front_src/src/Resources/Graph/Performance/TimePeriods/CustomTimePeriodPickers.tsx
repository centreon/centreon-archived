import * as React from 'react';

import dayjs from 'dayjs';
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter';
import { and, or } from 'ramda';
import { useTranslation } from 'react-i18next';

import { MuiPickersUtilsProvider } from '@material-ui/pickers';
import {
  FormHelperText,
  makeStyles,
  Typography,
  Button,
  Popover,
} from '@material-ui/core';
import AccessTimeIcon from '@material-ui/icons/AccessTime';

import { useUserContext } from '@centreon/ui-context';
import { dateTimeFormat, useLocaleDateTimeFormat } from '@centreon/ui';

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
  property: CustomTimePeriodProperty;
  date: Date;
}

interface Props {
  customTimePeriod: CustomTimePeriod;
  acceptDate: (props: AcceptDateProps) => void;
  isCompact: boolean;
}

dayjs.extend(isSameOrAfter);

const useStyles = makeStyles((theme) => ({
  pickers: {
    display: 'grid',
    gridTemplateColumns: `minmax(${theme.spacing(15)}px, ${theme.spacing(
      17,
    )}px) min-content minmax(${theme.spacing(15)}px, ${theme.spacing(17)}px)`,
    columnGap: `${theme.spacing(0.5)}px`,
    alignItems: 'center',
  },
  error: {
    textAlign: 'center',
  },
  minimalPickers: {
    display: 'grid',
    gridTemplateColumns: 'min-content auto',
    columnGap: `${theme.spacing(1)}px`,
    alignItems: 'center',
  },
  minimalFromTo: {
    display: 'grid',
    gridTemplateRows: 'repeat(2, min-content)',
    rowGap: `${theme.spacing(0.3)}px`,
  },
  pickerText: {
    lineHeight: '1.2',
    cursor: 'pointer',
  },
  buttonContent: {
    display: 'grid',
    gridTemplateColumns: 'min-content auto',
    columnGap: `${theme.spacing(1)}px`,
    alignItems: 'center',
  },
  compactFromTo: {
    display: 'grid',
    grid: 'repeat(2, min-content) / min-content auto',
    columnGap: `${theme.spacing(0.5)}px`,
  },
  fromTo: {
    display: 'grid',
    gridTemplateColumns: 'repeat(4, auto)',
    columnGap: `${theme.spacing(0.5)}px`,
    alignItems: 'center',
  },
  button: {
    padding: theme.spacing(0, 0.5),
  },
  popover: {
    padding: theme.spacing(1, 2),
    display: 'grid',
    gridTemplateRows: 'auto auto auto',
    rowGap: `${theme.spacing(1)}px`,
    justifyItems: 'center',
  },
}));

const CustomTimePeriodPickers = ({
  customTimePeriod,
  acceptDate,
  isCompact: isMinimalWidth,
}: Props): JSX.Element => {
  const [anchorEl, setAnchorEl] = React.useState<Element | null>(null);
  const [start, setStart] = React.useState<Date>(customTimePeriod.start);
  const [end, setEnd] = React.useState<Date>(customTimePeriod.end);
  const classes = useStyles(isMinimalWidth);
  const { t } = useTranslation();
  const { locale } = useUserContext();
  const { format } = useLocaleDateTimeFormat();
  const Adapter = useDateTimePickerAdapter();

  const isInvalidDate = ({ startDate, endDate }) =>
    dayjs(startDate).isSameOrAfter(dayjs(endDate), 'minute');

  const changeDate = ({ property, date }) => () => {
    const currentDate = customTimePeriod[property];

    if (
      or(
        dayjs(date).isSame(dayjs(currentDate)),
        isInvalidDate({ startDate: start, endDate: end }),
      )
    ) {
      return;
    }
    acceptDate({
      date,
      property,
    });
  };

  React.useEffect(() => {
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

  const openPopover = (event: React.MouseEvent) => {
    setAnchorEl(event.currentTarget);
  };

  const closePopover = () => {
    setAnchorEl(null);
  };

  const displayPopover = Boolean(anchorEl);

  const error = isInvalidDate({ startDate: start, endDate: end });

  const commonPickersProps = {
    autoOk: true,
    error: undefined,
    InputProps: {
      disableUnderline: true,
    },
    format: dateTimeFormat,
  };

  return (
    <>
      <Button
        variant="outlined"
        color="primary"
        className={classes.button}
        onClick={openPopover}
        aria-label={t(labelCompactTimePeriod)}
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
        open={displayPopover}
        onClose={closePopover}
        anchorOrigin={{
          vertical: 'top',
          horizontal: 'center',
        }}
        transformOrigin={{
          vertical: 'top',
          horizontal: 'center',
        }}
      >
        <div className={classes.popover}>
          <MuiPickersUtilsProvider
            utils={Adapter}
            locale={locale.substring(0, 2)}
          >
            <div>
              <Typography>{t(labelFrom)}</Typography>
              <div aria-label={t(labelStartDate)}>
                <DateTimePickerInput
                  commonPickersProps={commonPickersProps}
                  date={start}
                  property={CustomTimePeriodProperty.start}
                  maxDate={customTimePeriod.end}
                  changeDate={changeDate}
                  setDate={setStart}
                />
              </div>
            </div>
            <div>
              <Typography>{t(labelTo)}</Typography>
              <div aria-label={t(labelEndDate)}>
                <DateTimePickerInput
                  commonPickersProps={commonPickersProps}
                  date={end}
                  property={CustomTimePeriodProperty.end}
                  minDate={customTimePeriod.start}
                  changeDate={changeDate}
                  setDate={setEnd}
                />
              </div>
            </div>
          </MuiPickersUtilsProvider>
          {error && (
            <FormHelperText error className={classes.error}>
              {t(labelEndDateGreaterThanStartDate)}
            </FormHelperText>
          )}
        </div>
      </Popover>
    </>
  );
};

export default CustomTimePeriodPickers;
