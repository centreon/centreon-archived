/* eslint-disable class-methods-use-this */
import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { not } from 'ramda';
import dayjs from 'dayjs';
// import {
//   MuiPickersUtilsProvider,
//   KeyboardTimePicker,
//   KeyboardDatePicker,
//   DatePickerProps,
//   TimePickerProps,
// } from '@material-ui/pickers';

import {
  DatePicker,
  LocalizationProvider,
  MuiPickersAdapter,
  TimePicker,
} from '@mui/lab';
import {
  Checkbox,
  FormControlLabel,
  FormHelperText,
  Grid,
  Alert,
  StandardTextFieldProps,
  TextFieldProps,
  useTheme,
} from '@mui/material';
import { Box } from '@mui/system';

import { Dialog, TextField, SelectField } from '@centreon/ui';
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
import useDateTimePickerAdapter from '../../../useDateTimePickerAdapter';

const maxEndDate = new Date('2100-01-01');

interface Props {
  canConfirm: boolean;
  errors?;
  handleChange;
  onCancel;
  onConfirm;
  resources: Array<Resource>;
  setFieldValue;
  submitting: boolean;
  values;
}

const pickerCommonProps = {
  InputProps: {
    disableUnderline: true,
  },
  TextFieldComponent: TextField,
  inputVariant: 'filled',
  margin: 'none',
  variant: 'inline',
};

// const datePickerProps = {
//   ...pickerCommonProps,
//   disableToolbar: true,
//   format: 'L',
// } as Omit<DatePickerProps, 'onChange' | 'value'>;

// const timePickerProps = {
//   ...pickerCommonProps,
//   format: 'LT',
// } as Omit<TimePickerProps, 'onChange' | 'value'>;

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

  const { getDowntimeDeniedTypeAlert, canDowntimeServices } = useAclQuery();
  const [isPickerOpened, setIsPickerOpened] = React.useState(false);
  const { locale, timezone } = useUserContext();
  const theme = useTheme();

  const { Adapter } = useDateTimePickerAdapter({ locale, tz: timezone });

  const open = resources.length > 0;

  const hasHosts = resources.find((resource) => resource.type === 'host');

  const changeDate =
    (field) =>
    (value): void => {
      setFieldValue(field, value);
    };

  const deniedTypeAlert = getDowntimeDeniedTypeAlert(resources);

  const renderInput =
    (field: string) =>
    ({ inputRef, inputProps, InputProps }: TextFieldProps): JSX.Element =>
      (
        <TextField
          EndAdornment={(): JSX.Element => <>{InputProps?.endAdornment}</>}
          error={errors[field]}
          inputProps={{
            ...inputProps,
            ref: inputRef,
            style: { padding: theme.spacing(1) },
          }}
        />
      );

  const handleInputChange =
    ({ field, format = undefined }: { field: string; format?: string }) =>
    (newValue: dayjs.Dayjs | null, keyBoardValue: string | undefined): void => {
      console.log(isPickerOpened, dayjs(keyBoardValue, 'H:mm'));
      const value = dayjs(
        isPickerOpened ? newValue : keyBoardValue,
        format,
      ).locale(locale);

      changeDate(field)(value);
    };

  return (
    <Dialog
      confirmDisabled={!canConfirm}
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelSetDowntime)}
      labelTitle={t(labelDowntime)}
      open={open}
      submitting={submitting}
      onCancel={onCancel}
      onClose={onCancel}
      onConfirm={onConfirm}
    >
      {deniedTypeAlert && <Alert severity="warning">{deniedTypeAlert}</Alert>}
      <LocalizationProvider
        dateAdapter={Adapter}
        locale={locale.substring(0, 2)}
      >
        <Box
          sx={{
            display: 'grid',
            gap: 1,

            gridAutoColumns: 'auto',
            gridAutoRows: 'auto',
            gridTemplateColumns: '1.25fr 1fr',
          }}
        >
          <FormHelperText sx={{ gridColumn: 'span 2' }}>
            {t(labelFrom)}
          </FormHelperText>
          <DatePicker<dayjs.Dayjs>
            aria-label={t(labelStartDate)}
            maxDate={dayjs(maxEndDate)}
            renderInput={renderInput('dateStart')}
            value={values.dateStart}
            onChange={handleInputChange({ field: 'dateStart' })}
            onClose={(): void => setIsPickerOpened(false)}
            onOpen={(): void => setIsPickerOpened(true)}
          />
          <TimePicker<dayjs.Dayjs>
            aria-label={t(labelStartTime)}
            renderInput={renderInput('timeStart')}
            value={values.timeStart}
            onChange={handleInputChange({
              field: 'timeStart',
              format: 'HH:mm',
            })}
            onClose={(): void => setIsPickerOpened(false)}
            onOpen={(): void => setIsPickerOpened(true)}
          />
          <FormHelperText sx={{ gridColumn: 'span 2' }}>
            {t(labelTo)}
          </FormHelperText>
          <DatePicker<dayjs.Dayjs>
            aria-label={t(labelEndDate)}
            renderInput={renderInput('dateEnd')}
            value={values.endDate}
            onChange={handleInputChange({ field: 'dateEnd' })}
            onClose={(): void => setIsPickerOpened(false)}
            onOpen={(): void => setIsPickerOpened(true)}
          />
          <TimePicker<dayjs.Dayjs>
            aria-label={t(labelEndTime)}
            renderInput={renderInput('timeEnd')}
            value={values.endTime}
            onChange={handleInputChange({ field: 'timeEnd', format: 'HH:mm' })}
            onClose={(): void => setIsPickerOpened(false)}
            onOpen={(): void => setIsPickerOpened(true)}
          />
          <FormControlLabel
            control={
              <Checkbox
                checked={values.fixed}
                color="primary"
                inputProps={{ 'aria-label': t(labelFixed) }}
                size="small"
                onChange={handleChange('fixed')}
              />
            }
            label={t(labelFixed) as string}
          />
          <FormHelperText sx={{ gridColumn: 'span 2' }}>
            {t(labelDuration)}
          </FormHelperText>
          <TextField
            disabled={values.fixed}
            error={errors?.duration?.value}
            type="number"
            value={values.duration.value}
            onChange={handleChange('duration.value')}
          />
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
          <TextField
            fullWidth
            multiline
            error={errors?.comment}
            label={t(labelComment)}
            rows={3}
            sx={{ gridColumn: 'span 2' }}
            value={values.comment}
            onChange={handleChange('comment')}
          />
          {hasHosts && (
            <FormControlLabel
              control={
                <Checkbox
                  checked={
                    canDowntimeServices() && values.downtimeAttachedResources
                  }
                  color="primary"
                  disabled={!canDowntimeServices()}
                  inputProps={{ 'aria-label': labelSetDowntimeOnServices }}
                  size="small"
                  onChange={handleChange('downtimeAttachedResources')}
                />
              }
              label={t(labelSetDowntimeOnServices) as string}
            />
          )}
        </Box>
      </LocalizationProvider>
    </Dialog>
  );
};

export default DialogDowntime;
