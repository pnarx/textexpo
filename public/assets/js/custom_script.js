
    $("#preApplicationForm").on('submit', (e) => {
      e.preventDefault()

      $.ajax({
        url: "/stand-basvuru",
        method: 'POST',
        beforeSend: () => {
          $("#loadingForm").text('Bekleyiniz...')
        },
        data: {
          form: $("#preApplicationForm").serializeArray()
        },
        success: (response) => {
          console.log(response);

          if (response.status) {
            Swal.fire({
              icon: "success",
              title: "Başarılı",
              text: response.message,
              showConfirmButton: true,
              confirmButtonText: 'Tamam'
            })
          } else {
            Swal.fire({
              icon: "error",
              title: "Hata",
              text: response.message,
              showConfirmButton: true,
              confirmButtonText: 'Tamam'
            })
          }

          $("#loadingForm").text('Başvur')
          $("#preApplicationForm input, #preApplicationForm textarea").val('')
          $("#counter").val('0')

        }
      })

    })

      $(document).ready(function() {
          $("#message").on('keyup', function(e) {
              var textLength = $(this).val().length;
              if(textLength == 255) {
                  $("#counter").css('color', 'red')
              } else {
                  $("#counter").css('color', 'black')
              }
              $("#counter").text(textLength)
              
          })
      });

  

      $(document).ready(function() {
            $('.maskPhone').mask('0 (000) 000 00 00', {
                translation: {
                    '0': {pattern: /\d/}
                },
                onKeyPress: function(val, e, field, options) {
                    if (!val.startsWith('0')) {
                        field.val('0' + val);
                    }
                }
            });
      })

  

    $(document).ready(function () {
      const $header = $('#header');

      $(window).on('scroll', function () {
        if ($(this).scrollTop() > 50) {
          $header.addClass('scrolled');
        } else {
          $header.removeClass('scrolled');
        }
      });
    });
