/* eslint-disable no-nested-ternary */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable no-restricted-globals */

import React from 'react';

import DeleteIcon from '@material-ui/icons/Delete';
import UpdateIcon from '@material-ui/icons/SystemUpdateAlt';
import CheckIcon from '@material-ui/icons/Check';
import InstallIcon from '@material-ui/icons/Add';
import {
  Card,
  CardActions,
  CardContent,
  Paper,
  Button,
  Typography,
  Grid,
  Chip,
  LinearProgress,
  Divider,
} from '@material-ui/core';

import Wrapper from '../Wrapper';

class ExtensionsHolder extends React.Component {
  parseDescription = (description) => {
    return description.replace(/^centreon\s+(\w+)/i, (_, $1) => $1);
  };

  getPropsFromLicense = (licenseInfo) => {
    if (licenseInfo && licenseInfo.required) {
      if (!licenseInfo.expiration_date) {
        return {
          color: '#f90026',
          label: 'License required',
        };
      }
      if (!isNaN(Date.parse(licenseInfo.expiration_date))) {
        const expirationDate = new Date(licenseInfo.expiration_date);

        return {
          color: '#84BD00',
          label: `License expires ${expirationDate.toISOString().slice(0, 10)}`,
        };
      }

      return {
        color: '#f90026',
        label: 'License not valid',
      };
    }

    return undefined;
  };

  render() {
    const {
      title,
      entities,
      onCardClicked,
      onDelete,
      onInstall,
      onUpdate,
      updating,
      installing,
      deletingEntityId,
      type,
    } = this.props;

    return (
      <Wrapper>
        <Grid
          container
          alignItems="center"
          direction="row"
          spacing={1}
          style={{ marginBottom: 8, width: '100%' }}
        >
          <Grid item>
            <Typography variant="body1">{title}</Typography>
          </Grid>
          <Grid item style={{ flexGrow: 1 }}>
            <Divider style={{ backgroundColor: 'rgba(0, 0, 0, 0.12)' }} />
          </Grid>
        </Grid>
        <Grid
          container
          alignItems="stretch"
          spacing={2}
          style={{ cursor: 'pointer' }}
        >
          {entities.map((entity) => {
            const isLoading =
              installing[entity.id] ||
              updating[entity.id] ||
              deletingEntityId === entity.id;

            const licenseInfo = this.getPropsFromLicense(entity.license);

            return (
              <Grid
                item
                id={`${type}-${entity.id}`}
                key={entity.id}
                style={{ width: 200 }}
                onClick={() => {
                  onCardClicked(entity.id, type);
                }}
              >
                <Card
                  style={{ display: 'grid', height: '100%' }}
                  variant="outlined"
                >
                  {isLoading && <LinearProgress />}
                  <CardContent style={{ padding: '10px' }}>
                    <Typography style={{ fontWeight: 'bold' }} variant="body1">
                      {this.parseDescription(entity.description)}
                    </Typography>
                    <Typography variant="body2">
                      {`by ${entity.label}`}
                    </Typography>
                  </CardContent>
                  <CardActions style={{ justifyContent: 'center' }}>
                    {entity.version.installed ? (
                      <Chip
                        avatar={
                          entity.version.outdated ? (
                            <UpdateIcon
                              style={{
                                color: '#FFFFFF',
                                cursor: 'pointer',
                              }}
                              onClick={(e) => {
                                e.preventDefault();
                                e.stopPropagation();

                                onUpdate(entity.id, type);
                              }}
                            />
                          ) : (
                            <CheckIcon style={{ color: '#FFFFFF' }} />
                          )
                        }
                        deleteIcon={<DeleteIcon style={{ color: '#FFFFFF' }} />}
                        disabled={isLoading}
                        label={entity.version.current}
                        style={{
                          backgroundColor: entity.version.outdated
                            ? '#FF9A13'
                            : '#84BD00',
                          color: '#FFFFFF',
                        }}
                        onDelete={() => onDelete(entity, type)}
                      />
                    ) : (
                      <Button
                        color="primary"
                        disabled={isLoading}
                        size="small"
                        startIcon={!entity.version.installed && <InstallIcon />}
                        variant="contained"
                        onClick={(e) => {
                          e.preventDefault();
                          e.stopPropagation();
                          const { id } = entity;
                          const { version } = entity;
                          if (version.outdated && !updating[entity.id]) {
                            onUpdate(id, type);
                          } else if (
                            !version.installed &&
                            !installing[entity.id]
                          ) {
                            onInstall(id, type);
                          }
                        }}
                      >
                        {entity.version.available}
                      </Button>
                    )}
                  </CardActions>

                  <Paper
                    square
                    style={{
                      alignItems: 'center',
                      backgroundColor: licenseInfo?.color || '#FFFFFF',
                      cursor: 'pointer',
                      display: 'flex',
                      justifyContent: 'center',
                      minHeight: '20px',
                    }}
                  >
                    {licenseInfo?.label && (
                      <Typography style={{ color: '#FFFFFF' }} variant="body2">
                        {licenseInfo.label}
                      </Typography>
                    )}
                  </Paper>
                </Card>
              </Grid>
            );
          })}
        </Grid>
      </Wrapper>
    );
  }
}

export default ExtensionsHolder;
