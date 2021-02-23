/* eslint-disable class-methods-use-this */
import * as React from 'react';

import dayjs from 'dayjs';
import DayjsAdapter from '@date-io/dayjs';
import { useTranslation } from 'react-i18next';

import {
  Checkbox,
  FormControlLabel,
  FormHelperText,
  Grid,
} from '@material-ui/core';
import {
  MuiPickersUtilsProvider,
  KeyboardTimePicker,
  KeyboardDatePicker,
  DatePickerProps,
  TimePickerProps,
} from '@material-ui/pickers';
import { Alert } from '@material-ui/lab';

import {
  Dialog,
  TextField,
  SelectField,
  useLocaleDateTimeFormat,
} from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import {
  labelCancel,
  labelEndDate,
  labelEndTime,
  labelStartDate,
  labelStartTime,
  labelChangeEndDate,
  labelChangeEndTime,
  labelChangeStartDate,
  labelChangeStartTime,
  labelComment,
  labelDowntime,
  labelDuration,
  labelFixed,
  labelFrom,
  labelHours,
  labelMinutes,
  labelSeconds,
  labelSetDowntime,
  labelSetDowntimeOnServices,
  labelTo,
} from '../../../translatedLabels';
import { Resource } from '../../../models';
import useAclQuery from '../aclQuery';

interface Props {
  resources: Array<Resource>;
  canConfirm: boolean;
  onCancel;
  onConfirm;
  errors?;
  values;
  handleChange;
  setFieldValue;
  submitting: boolean;
}

const pickerCommonProps = {
  disableToolbar: true,
  variant: 'inline',
  margin: 'none',
  inputVariant: 'filled',
  TextFieldComponent: TextField,
  InputProps: {
    disableUnderline: true,
  },
};

const datePickerProps = {
  ...pickerCommonProps,
  format: 'L',
} as Omit<DatePickerProps, 'onChange'>;

const timePickerProps = {
  ...pickerCommonProps,
  format: 'LT',
  ampm: false,
} as Omit<TimePickerProps, 'onChange'>;

const DialogDowntime = ({
  resources,
  canConfirm,
  onCancel,
  onConfirm,
  errors,
  values,
  submitting,
  handleChange,
  setFieldValue,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const { locale, timezone } = useUserContext();
  const { getDowntimeDeniedTypeAlert, canDowntimeServices } = useAclQuery();
  const { format } = useLocaleDateTimeFormat();

  const open = resources.length > 0;

  const hasHosts = resources.find((resource) => resource.type === 'host');

  const changeDate = (field) => (value): void => {
    setFieldValue(field, value);
  };

  const deniedTypeAlert = getDowntimeDeniedTypeAlert(resources);

  class Adapter extends DayjsAdapter {
    public format(date, formatString): string {
      return format({ date, formatString });
    }

    public date(value): dayjs.Dayjs {
      return dayjs(value).locale(locale).tz(timezone);
    }
  }

  return (
    <Dialog
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelSetDowntime)}
      labelTitle={t(labelDowntime)}
      open={open}
      onClose={onCancel}
      onCancel={onCancel}
      onConfirm={onConfirm}
      confirmDisabled={!canConfirm}
      submitting={submitting}
    >
      {deniedTypeAlert && <Alert severity="warning">{deniedTypeAlert}</Alert>}
      <MuiPickersUtilsProvider utils={Adapter} locale={locale.substring(0, 2)}>
        <Grid direction="column" container spacing={1}>
          <Grid item>
            <FormHelperText>{t(labelFrom)}</FormHelperText>
            <Grid direction="row" container spacing={1}>
              <Grid item style={{ width: 240 }}>
                <KeyboardDatePicker
                  aria-label={t(labelStartDate)}
                  value={values.dateStart}
                  onChange={changeDate('dateStart')}
                  inputMode="text"
                  KeyboardButtonProps={{
                    'aria-label': t(labelChangeStartDate),
                  }}
                  error={errors?.dateStart !== undefined}
                  helperText={errors?.dateStart}
                  {...datePickerProps}
                />
              </Grid>
              <Grid item style={{ width: 200 }}>
                <KeyboardTimePicker
                  aria-label={t(labelStartTime)}
                  value={values.timeStart}
                  onChange={changeDate('timeStart')}
                  KeyboardButtonProps={{
                    'aria-label': t(labelChangeStartTime),
                  }}
                  error={errors?.timeStart !== undefined}
                  helperText={errors?.timeStart}
                  {...timePickerProps}
                />
              </Grid>
            </Grid>
          </Grid>
          <Grid item>
            <FormHelperText>{t(labelTo)}</FormHelperText>
            <Grid direction="row" container spacing={1}>
              <Grid item style={{ width: 240 }}>
                <KeyboardDatePicker
                  aria-label={t(labelEndDate)}
                  value={values.dateEnd}
                  onChange={changeDate('dateEnd')}
                  KeyboardButtonProps={{
                    'aria-label': t(labelChangeEndDate),
                  }}
                  error={errors?.dateEnd !== undefined}
                  helperText={errors?.dateEnd}
                  {...datePickerProps}
                />
              </Grid>
              <Grid item style={{ width: 200 }}>
                <KeyboardTimePicker
                  aria-label={t(labelEndTime)}
                  value={values.timeEnd}
                  onChange={changeDate('timeEnd')}
                  KeyboardButtonProps={{
                    'aria-label': t(labelChangeEndTime),
                  }}
                  error={errors?.timeEnd !== undefined}
                  helperText={errors?.timeEnd}
                  {...timePickerProps}
                />
              </Grid>
            </Grid>
          </Grid>
          <Grid item>
            <FormControlLabel
              control={
                <Checkbox
                  checked={values.fixed}
                  inputProps={{ 'aria-label': t(labelFixed) }}
                  color="primary"
                  onChange={handleChange('fixed')}
                  size="small"
                />
              }
              label={t(labelFixed)}
            />
          </Grid>
          <Grid item>
            <FormHelperText>{t(labelDuration)}</FormHelperText>
            <Grid direction="row" container spacing={1}>
              <Grid item style={{ width: 150 }}>
                <TextField
                  disabled={values.fixed}
                  type="number"
                  onChange={handleChange('duration.value')}
                  value={values.duration.value}
                  error={errors?.duration?.value}
                />
              </Grid>
              <Grid item style={{ width: 150 }}>
                <SelectField
                  disabled={values.fixed}
                  options={[
                    {
                      id: 'seconds',
                      name: t(labelSeconds),
                    },
                    {
                      id: 'minutes',
                      name: t(labelMinutes),
                    },
                    {
                      id: 'hours',
                      name: t(labelHours),
                    },
                  ]}
                  selectedOptionId={values.duration.unit}
                  onChange={handleChange('duration.unit')}
                />
              </Grid>
            </Grid>
          </Grid>
          <Grid item>
            <TextField
              value={values.comment}
              onChange={handleChange('comment')}
              multiline
              label={t(labelComment)}
              fullWidth
              rows={3}
              error={errors?.comment}
            />
          </Grid>
          {hasHosts && (
            <Grid item>
              <FormControlLabel
                control={
                  <Checkbox
                    checked={
                      canDowntimeServices() && values.downtimeAttachedResources
                    }
                    disabled={!canDowntimeServices()}
                    inputProps={{ 'aria-label': labelSetDowntimeOnServices }}
                    color="primary"
                    onChange={handleChange('downtimeAttachedResources')}
                    size="small"
                  />
                }
                label={t(labelSetDowntimeOnServices)}
              />
            </Grid>
          )}
        </Grid>
      </MuiPickersUtilsProvider>
    </Dialog>
  );
};

export default DialogDowntime;
