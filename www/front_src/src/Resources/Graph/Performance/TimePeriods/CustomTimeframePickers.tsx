import * as React from 'react';

import DayjsUtils from '@date-io/dayjs';
import dayjs from 'dayjs';
import { equals, or } from 'ramda';
import { useTranslation } from 'react-i18next';

import { DateTimePicker, MuiPickersUtilsProvider } from '@material-ui/pickers';
import { MaterialUiPickersDate } from '@material-ui/pickers/typings/date';
import { FormHelperText, makeStyles } from '@material-ui/core';

import { useUserContext } from '@centreon/ui-context';

import {
  labelCancel,
  labelEndDate,
  labelOk,
  labelStartDate,
} from '../../../translatedLabels';
import {
  Timeframe,
  TimeframeProperties,
} from '../../../Details/tabs/Graph/models';

interface AcceptDateProps {
  property: TimeframeProperties;
  date: Date;
}

interface Props {
  timeframe: Timeframe;
  acceptDate: (props: AcceptDateProps) => void;
}

const useStyles = makeStyles((theme) => ({
  pickers: {
    display: 'grid',
    gridTemplateColumns: `repeat(2, ${theme.spacing(16)}px)`,
    columnGap: `${theme.spacing(3)}px`,
  },
  error: {
    textAlign: 'center',
  },
}));

const CustomTimeframePickers = ({
  timeframe,
  acceptDate,
}: Props): JSX.Element => {
  const { locale } = useUserContext();
  const classes = useStyles();
  const { t } = useTranslation();

  const startIsAfterEnd = dayjs(timeframe.start).isAfter(dayjs(timeframe.end));

  const changeDate = (property: TimeframeProperties) => (
    value: MaterialUiPickersDate,
  ) => {
    if (
      or(
        startIsAfterEnd,
        equals(value?.toDate().getTime(), timeframe[property].getTime()),
      )
    ) {
      return;
    }
    acceptDate({ date: value?.toDate() || new Date(), property });
  };

  const commonPickersProps = {
    autoOk: true,
    ampm: false,
    format: 'YYYY/MM/DD HH:mm',
    okLabel: t(labelOk),
    cancelLabel: t(labelCancel),
  };

  return (
    <div>
      <div className={classes.pickers}>
        <MuiPickersUtilsProvider
          utils={DayjsUtils}
          locale={locale.substring(0, 2)}
        >
          <DateTimePicker
            {...commonPickersProps}
            value={timeframe.start}
            onChange={changeDate(TimeframeProperties.start)}
            label={t(labelStartDate)}
            maxDate={timeframe.end}
            size="small"
            inputProps={{
              'aria-label': t(labelStartDate),
            }}
          />
          <DateTimePicker
            {...commonPickersProps}
            value={timeframe.end}
            onChange={changeDate(TimeframeProperties.end)}
            label={t(labelEndDate)}
            minDate={timeframe.start}
            size="small"
            inputProps={{
              'aria-label': t(labelEndDate),
            }}
          />
        </MuiPickersUtilsProvider>
      </div>
      {startIsAfterEnd && (
        <FormHelperText error className={classes.error}>
          Start date is after end date
        </FormHelperText>
      )}
    </div>
  );
};

export default CustomTimeframePickers;
