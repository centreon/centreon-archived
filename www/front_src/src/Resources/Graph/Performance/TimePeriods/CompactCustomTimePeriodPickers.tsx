import * as React from 'react';

import { useTranslation } from 'react-i18next';

import {
  makeStyles,
  Typography,
  Button,
  Popover,
  FormHelperText,
} from '@material-ui/core';
import AccessTimeIcon from '@material-ui/icons/AccessTime';
import { MuiPickersUtilsProvider } from '@material-ui/pickers';

import { dateTimeFormat, useLocaleDateTimeFormat } from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context/src';

import {
  CustomTimePeriod,
  CustomTimePeriodProperty,
} from '../../../Details/tabs/Graph/models';
import {
  labelCompactTimePeriod,
  labelEndDate,
  labelEndDateGreaterThanStartDate,
  labelFrom,
  labelStartDate,
  labelTo,
} from '../../../translatedLabels';
import useDateTimePickerAdapter from '../../../useDateTimePickerAdapter';

import DateTimePickerInput from './DateTimePickerInput';

const useStyles = makeStyles((theme) => ({
  buttonContent: {
    display: 'grid',
    gridTemplateColumns: 'min-content auto',
    columnGap: `${theme.spacing(1)}px`,
    alignItems: 'center',
  },
  fromTo: {
    display: 'grid',
    grid: 'repeat(2, min-content) / min-content auto',
    columnGap: `${theme.spacing(0.5)}px`,
  },
  pickerText: {
    lineHeight: '1.2',
    cursor: 'pointer',
  },
  button: {
    padding: theme.spacing(0, 0.5),
  },
  popover: {
    padding: theme.spacing(1, 2),
    display: 'grid',
    gridTemplateRows: 'auto auto',
    rowGap: `${theme.spacing(1)}px`,
  },
  error: {
    textAlign: 'center',
  },
}));

interface Props {
  customTimePeriod: CustomTimePeriod;
  start: Date;
  end: Date;
  commonPickersProps;
  error: boolean;
  onChangeDate: (props) => () => void;
  setStart: React.Dispatch<React.SetStateAction<Date>>;
  setEnd: React.Dispatch<React.SetStateAction<Date>>;
}

const MinimalCustomTimePeriodPickers = ({
  customTimePeriod,
  start,
  end,
  commonPickersProps,
  error,
  onChangeDate,
  setStart,
  setEnd,
}: Props): JSX.Element => {
  const [anchorEl, setAnchorEl] = React.useState<Element | null>(null);
  const classes = useStyles();
  const { t } = useTranslation();
  const { locale } = useUserContext();
  const { format } = useLocaleDateTimeFormat();
  const Adapter = useDateTimePickerAdapter();

  const openPopover = (event: React.MouseEvent) => {
    setAnchorEl(event.currentTarget);
  };

  const closePopover = () => {
    setAnchorEl(null);
  };

  const displayPopover = Boolean(anchorEl);

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
          <div className={classes.fromTo}>
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
                  changeDate={onChangeDate}
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
                  changeDate={onChangeDate}
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

export default MinimalCustomTimePeriodPickers;
