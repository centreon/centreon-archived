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
  classNamePaper?: string;
  classNamePicker?: string;
  customTimePeriod: CustomTimePeriod;
  onClose?: () => void;
  open: boolean;
  reference?: AnchorReference;
  renderBody?: JSX.Element;
  renderFooter?: JSX.Element;
  renderTitle?: JSX.Element;
  transformOrigin?: PopoverOrigin;
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
}: Props): JSX.Element => {
  const { classes, cx } = useStyles();
  const { t } = useTranslation();
  const [start, setStart] = useState<Date>(customTimePeriod.start);
  const [end, setEnd] = useState<Date>(customTimePeriod.end);

  const { locale } = useAtomValue(userAtom);
  const { Adapter } = useDateTimePickerAdapter();

  const isInvalidDate = ({ startDate, endDate }): boolean =>
    dayjs(startDate).isSameOrAfter(dayjs(endDate), 'minute');

  const error = isInvalidDate({ endDate: end, startDate: start });

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
        {renderBody}
        {renderFooter}
      </Popover>
    </div>
  );
};

export default PopoverCustomTimePeriodPickers;
