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
import AccessTimeIcon from '@material-ui/icons/esm/AccessTime';

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
    columnGap: `${theme.spacing(1)}px`,
    display: 'grid',
    gridTemplateColumns: 'min-content auto',
  },
  compactFromTo: {
    columnGap: `${theme.spacing(0.5)}px`,
    display: 'grid',
    grid: 'repeat(2, min-content) / min-content auto',
  },
  error: {
    textAlign: 'center',
  },
  fromTo: {
    alignItems: 'center',
    columnGap: `${theme.spacing(0.5)}px`,
    display: 'grid',
    gridTemplateColumns: 'repeat(4, auto)',
  },
  minimalFromTo: {
    display: 'grid',
    gridTemplateRows: 'repeat(2, min-content)',
    rowGap: `${theme.spacing(0.3)}px`,
  },
  minimalPickers: {
    alignItems: 'center',
    columnGap: `${theme.spacing(1)}px`,
    display: 'grid',
    gridTemplateColumns: 'min-content auto',
  },
  pickerText: {
    cursor: 'pointer',
    lineHeight: '1.2',
  },
  pickers: {
    alignItems: 'center',
    columnGap: `${theme.spacing(0.5)}px`,
    display: 'grid',
    gridTemplateColumns: `minmax(${theme.spacing(15)}px, ${theme.spacing(
      17,
    )}px) min-content minmax(${theme.spacing(15)}px, ${theme.spacing(17)}px)`,
  },
  popover: {
    display: 'grid',
    gridTemplateRows: 'auto auto auto',
    justifyItems: 'center',
    padding: theme.spacing(1, 2),
    rowGap: `${theme.spacing(1)}px`,
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

  const changeDate =
    ({ property, date }) =>
    () => {
      const currentDate = customTimePeriod[property];

      if (
        or(
          dayjs(date).isSame(dayjs(currentDate)),
          isInvalidDate({ endDate: end, startDate: start }),
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

  const error = isInvalidDate({ endDate: end, startDate: start });

  const commonPickersProps = {
    InputProps: {
      disableUnderline: true,
    },
    autoOk: true,
    error: undefined,
    format: dateTimeFormat,
  };

  return (
    <>
      <Button
        aria-label={t(labelCompactTimePeriod)}
        className={classes.button}
        color="primary"
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
        <div className={classes.popover}>
          <MuiPickersUtilsProvider
            locale={locale.substring(0, 2)}
            utils={Adapter}
          >
            <div>
              <Typography>{t(labelFrom)}</Typography>
              <div aria-label={t(labelStartDate)}>
                <DateTimePickerInput
                  changeDate={changeDate}
                  commonPickersProps={commonPickersProps}
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
                  commonPickersProps={commonPickersProps}
                  date={end}
                  minDate={customTimePeriod.start}
                  property={CustomTimePeriodProperty.end}
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
