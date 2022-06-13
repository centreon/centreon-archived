import { useState, useEffect } from 'react';

import { isNil, isEmpty, values } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useUpdateAtom } from 'jotai/utils';

import { Typography, FormControlLabel, Checkbox } from '@mui/material';
import Radio from '@mui/material/Radio';

import { postData, useRequest, TextField, SelectField } from '@centreon/ui';

import { setRemoteServerWizardDerivedAtom } from '../pollerAtoms';
import { useStyles } from '../../styles/partials/form/PollerWizardStyle';
import {
  labelCentreonCentralIp,
  labelCentreonFolder,
  labelCreateRemoteServer,
  labelDbPassword,
  labelDbUser,
  labelServerName,
  labelDoNotUseConfiguredProxy,
  labelSelectRemoteLinks,
  labelSelectRemoteServer,
  labelServerIp,
  labelCheckCertificate,
  labelServerConfiguration,
  labelRequired,
} from '../translatedLabels';
import { Props, WaitList, WizardButtonsTypes } from '../models';
import WizardButtons from '../forms/wizardButtons';
import { remoteServerWaitListEndpoint } from '../api/endpoints';

interface RemoteServerStepOneFormData {
  centreon_central_ip: string;
  centreon_folder: string;
  db_password: string;
  db_user: string;
  no_check_certificate: boolean;
  no_proxy: boolean;
  server_ip: string;
  server_name: string;
}

interface RemoteServerStepOneFormDataError {
  centreon_central_ip: string;
  centreon_folder: string;
  db_password: string;
  db_user: string;
  server_ip: string;
  server_name: string;
}

const RemoteServerWizardStepOne = ({
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [waitList, setWaitList] = useState<Array<WaitList> | null>(null);
  const [initialized, setInitialized] = useState(false);
  const [inputTypeManual, setInputTypeManual] = useState(true);

  const [stepOneFormData, setStepOneFormData] =
    useState<RemoteServerStepOneFormData>({
      centreon_central_ip: '',
      centreon_folder: '/centreon/',
      db_password: '',
      db_user: '',
      no_check_certificate: false,
      no_proxy: false,
      server_ip: '',
      server_name: '',
    });

  const [error, setError] = useState<RemoteServerStepOneFormDataError>({
    centreon_central_ip: '',
    centreon_folder: '',
    db_password: '',
    db_user: '',
    server_ip: '',
    server_name: '',
  });

  const { sendRequest } = useRequest<Array<WaitList>>({
    request: postData,
  });

  const setWizard = useUpdateAtom(setRemoteServerWizardDerivedAtom);

  const getWaitList = (): void => {
    sendRequest({
      data: null,
      endpoint: remoteServerWaitListEndpoint,
    })
      .then((data): void => {
        setWaitList(data);
      })
      .catch(() => {
        setWaitList([]);
      });
  };

  const initializeFromRest = (value): void => {
    setInitialized(true);
    setInputTypeManual(!value);
  };

  const handleChange = (event): void => {
    const { value, name } = event.target;

    if (name === 'no_check_certificate' || name === 'no_proxy') {
      setStepOneFormData({
        ...stepOneFormData,
        [name]: !stepOneFormData[name],
      });

      return;
    }

    setError({
      ...error,
      [name]: isEmpty(value.trim()) ? t(labelRequired) : '',
    });

    setStepOneFormData({
      ...stepOneFormData,
      [name]: value,
    });
  };

  const handleBlur = (event): void => {
    const { value, name } = event.target;

    setError({
      ...error,
      [name]: isEmpty(value.trim()) ? t(labelRequired) : '',
    });
  };

  const handleSubmit = (event): void => {
    event.preventDefault();

    setWizard(stepOneFormData);
    goToNextStep();
  };

  const waitListOption = waitList?.map((c) => ({ id: c.ip, name: c.ip }));

  const isFormNotCompleted = values(stepOneFormData).some((x) => x === '');
  const hasError = values(error).some((x) => x !== '');

  const getError = (stateName): string | undefined =>
    error[stateName].length > 0 ? error[stateName] : undefined;

  useEffect(() => {
    getWaitList();
  }, []);

  useEffect(() => {
    if (waitList) {
      const platform = waitList.find(
        (server) => server.ip === stepOneFormData.server_ip,
      );
      if (platform) {
        setStepOneFormData({
          ...stepOneFormData,
          server_name: platform.server_name,
        });
      }
    }
  }, [stepOneFormData.server_ip]);

  useEffect(() => {
    if (isNil(waitList) || initialized) {
      return;
    }
    initializeFromRest(waitList.length > 0);
  }, [waitList]);

  return (
    <div>
      <div className={classes.formHeading}>
        <Typography variant="h6">{t(labelServerConfiguration)}</Typography>
      </div>
      <form autoComplete="off" onSubmit={handleSubmit}>
        <div className={classes.wizardRadio}>
          <FormControlLabel
            checked={inputTypeManual}
            control={<Radio color="primary" size="small" />}
            label={`${t(labelCreateRemoteServer)}`}
            onClick={(): void => setInputTypeManual(true)}
          />
          <FormControlLabel
            checked={!inputTypeManual}
            control={<Radio color="primary" size="small" />}
            label={`${t(labelSelectRemoteServer)}`}
            onClick={(): void => setInputTypeManual(false)}
          />
        </div>
        {inputTypeManual ? (
          <div className={classes.form}>
            <TextField
              fullWidth
              required
              error={getError('server_name')}
              label={t(labelServerName)}
              name="server_name"
              value={stepOneFormData.server_name}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('server_ip')}
              label={t(labelServerIp)}
              name="server_ip"
              value={stepOneFormData.server_ip}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('db_user')}
              label={t(labelDbUser)}
              name="db_user"
              value={stepOneFormData.db_user}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('db_password')}
              label={t(labelDbPassword)}
              name="db_password"
              type="password"
              value={stepOneFormData.db_password}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('centreon_central_ip')}
              label={t(labelCentreonCentralIp)}
              name="centreon_central_ip"
              value={stepOneFormData.centreon_central_ip}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('centreon_folder')}
              label={t(labelCentreonFolder)}
              name="centreon_folder"
              value={stepOneFormData.centreon_folder}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <FormControlLabel
              control={
                <Checkbox
                  checked={stepOneFormData.no_check_certificate}
                  name="no_check_certificate"
                  onChange={handleChange}
                />
              }
              label={`${t(labelCheckCertificate)}`}
            />
            <FormControlLabel
              control={
                <Checkbox
                  checked={stepOneFormData.no_proxy}
                  name="no_proxy"
                  onChange={handleChange}
                />
              }
              label={`${t(labelDoNotUseConfiguredProxy)}`}
            />
          </div>
        ) : (
          <div className={classes.form}>
            <SelectField
              fullWidth
              label={t(labelSelectRemoteLinks)}
              name="server_ip"
              options={waitListOption || []}
              selectedOptionId={stepOneFormData.server_ip}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('server_name')}
              label={t(labelServerName)}
              name="server_name"
              value={stepOneFormData.server_name}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('server_ip')}
              label={t(labelServerIp)}
              name="server_ip"
              value={stepOneFormData.server_ip}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('db_user')}
              label={t(labelDbUser)}
              name="db_user"
              value={stepOneFormData.db_user}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('db_password')}
              label={t(labelDbPassword)}
              name="db_password"
              type="password"
              value={stepOneFormData.db_password}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('centreon_central_ip')}
              label={t(labelCentreonCentralIp)}
              name="centreon_central_ip"
              value={stepOneFormData.centreon_central_ip}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('centreon_folder')}
              label={t(labelCentreonFolder)}
              name="centreon_folder"
              value={stepOneFormData.centreon_folder}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <FormControlLabel
              control={
                <Checkbox
                  checked={stepOneFormData.no_check_certificate}
                  name="no_check_certificate"
                  onChange={handleChange}
                />
              }
              label={`${t(labelCheckCertificate)}`}
            />
            <FormControlLabel
              control={
                <Checkbox
                  checked={stepOneFormData.no_proxy}
                  name="no_proxy"
                  onChange={handleChange}
                />
              }
              label={`${t(labelDoNotUseConfiguredProxy)}`}
            />
          </div>
        )}
        <WizardButtons
          disabled={isFormNotCompleted || hasError}
          goToPreviousStep={goToPreviousStep}
          type={WizardButtonsTypes.Next}
        />
      </form>
    </div>
  );
};

export default RemoteServerWizardStepOne;
